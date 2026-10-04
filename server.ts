import express from 'express';
import type { Request, Response } from 'express';
import path from 'path';
import bcrypt from 'bcryptjs';
import { db, syncToSupabase, recordSupabaseAction } from './src/store.ts';
import type { User } from './src/store.ts';
import { 
  renderHomePage, 
  renderAboutPage, 
  renderContactPage, 
  renderLoginPage, 
  renderRegisterPage 
} from './src/pages.ts';
import { 
  renderProviderDashboard, 
  renderProviderAddFood, 
  renderGroupDashboard, 
  renderAdminDashboard 
} from './src/portals.ts';

const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// Serve static assets (CSS, JS, images)
app.use('/css', express.static(path.join(process.cwd(), 'css')));
app.use('/js', express.static(path.join(process.cwd(), 'js')));
app.use('/images', express.static(path.join(process.cwd(), 'images')));

// Simple in-memory session mapping
const sessions = new Map<string, number>();

function parseCookies(req: Request): Record<string, string> {
  const list: Record<string, string> = {};
  const rc = req.headers.cookie;
  if (rc) {
    rc.split(';').forEach((cookie) => {
      const parts = cookie.split('=');
      list[parts.shift()!.trim()] = decodeURIComponent(parts.join('='));
    });
  }
  return list;
}

function getSessionUser(req: Request): User | null {
  const cookies = parseCookies(req);
  const sid = (req.query.sid as string) || cookies['SEVAM_SID'];
  if (!sid || !sessions.has(sid)) return null;
  const userId = sessions.get(sid)!;
  return db.users.find(u => u.id === userId) || null;
}

function setSessionCookie(res: Response, sid: string) {
  res.setHeader(
    'Set-Cookie',
    `SEVAM_SID=${encodeURIComponent(sid)}; Path=/; Max-Age=2592000; HttpOnly; SameSite=None; Secure; Partitioned`
  );
}

// Public Routes
app.get('/', (req, res) => {
  const user = getSessionUser(req);
  const query = (req.query.q as string) || '';
  const cat = (req.query.cat as string) || '';
  res.send(renderHomePage(user, query, cat));
});

app.get('/about', (req, res) => {
  const user = getSessionUser(req);
  res.send(renderAboutPage(user));
});
app.get('/about.php', (req, res) => res.redirect('/about'));

app.get('/contact', (req, res) => {
  const user = getSessionUser(req);
  res.send(renderContactPage(user));
});
app.get('/contact.php', (req, res) => res.redirect('/contact'));

app.post('/contact', async (req, res) => {
  const { name, email, phone, subject, message } = req.body;
  if (name && email && message) {
    const newMsg = {
      id: db.nextMsgId++,
      name,
      email,
      phone: phone || '',
      subject: subject || 'General Inquiry',
      message,
      created_at: new Date().toISOString()
    };
    db.messages.push(newMsg);
    await syncToSupabase('contact_messages', 'POST', newMsg);
    await recordSupabaseAction('contact_message_sent', { name, email, subject }, 'message', newMsg.id);
    const user = getSessionUser(req);
    return res.send(renderContactPage(user, 'Thank you! Your message has been received.'));
  }
  const user = getSessionUser(req);
  res.send(renderContactPage(user, '', 'Please fill in all required fields.'));
});

// Auth Routes
app.get('/login', (req, res) => {
  const user = getSessionUser(req);
  if (user) {
    if (user.role === 'provider') return res.redirect('/provider/dashboard');
    if (user.role === 'group') return res.redirect('/group/dashboard');
    if (user.role === 'admin') return res.redirect('/admin/dashboard');
  }
  res.send(renderLoginPage(null));
});
app.get('/login.php', (req, res) => res.redirect('/login'));

app.post('/login', async (req, res) => {
  const { identity, password } = req.body;
  const user = db.users.find(u => u.username === identity || u.email === identity);
  if (user && bcrypt.compareSync(password, user.password)) {
    const sid = Math.random().toString(36).substring(2) + Date.now().toString(36);
    sessions.set(sid, user.id);
    setSessionCookie(res, sid);
    await recordSupabaseAction('user_login', { username: user.username, role: user.role }, 'auth', user.id);
    if (user.role === 'provider') return res.redirect('/provider/dashboard');
    if (user.role === 'group') return res.redirect('/group/dashboard');
    if (user.role === 'admin') return res.redirect('/admin/dashboard');
    return res.redirect('/');
  }
  res.send(renderLoginPage(null, 'Invalid username/email or password.'));
});

app.get('/register', (req, res) => {
  const user = getSessionUser(req);
  const role = (req.query.role as string) || 'provider';
  res.send(renderRegisterPage(user, role));
});
app.get('/register.php', (req, res) => res.redirect('/register'));

app.post('/register', async (req, res) => {
  const { role, org_name, contact_name, username, email, password } = req.body;
  if (!username || !email || !password) {
    return res.send(renderRegisterPage(null, role, 'All fields are required.'));
  }
  if (db.users.some(u => u.username === username || u.email === email)) {
    return res.send(renderRegisterPage(null, role, 'Username or email already exists.'));
  }

  const newUser: User = {
    id: db.nextUserId++,
    username,
    email,
    password: bcrypt.hashSync(password, 10),
    role: role === 'group' ? 'group' : 'provider',
    status: 'active',
    created_at: new Date().toISOString()
  };
  db.users.push(newUser);
  await syncToSupabase('users', 'POST', { id: newUser.id, username, email, role: newUser.role, status: 'active' });

  if (newUser.role === 'provider') {
    const newProv = {
      id: db.providers.length + 1,
      user_id: newUser.id,
      business_name: org_name || username,
      owner_name: contact_name || username,
      phone: '',
      address: '',
      city: 'Indore',
      state: 'Madhya Pradesh',
      business_type: 'Community Kitchen',
      fssai_status: 'pending',
      fssai_number: ''
    };
    db.providers.push(newProv);
    await syncToSupabase('food_providers', 'POST', newProv);
  } else {
    const newGrp = {
      id: db.groups.length + 1,
      user_id: newUser.id,
      group_name: org_name || username,
      representative_name: contact_name || username,
      phone: '',
      address: '',
      city: 'Indore',
      state: 'Madhya Pradesh',
      organization_type: 'Volunteer Group',
      darpan_id: ''
    };
    db.groups.push(newGrp);
    await syncToSupabase('social_working_groups', 'POST', newGrp);
  }

  await recordSupabaseAction('user_registered', { username, role: newUser.role }, 'user', newUser.id);

  const sid = Math.random().toString(36).substring(2) + Date.now().toString(36);
  sessions.set(sid, newUser.id);
  setSessionCookie(res, sid);

  if (newUser.role === 'provider') return res.redirect('/provider/dashboard');
  return res.redirect('/group/dashboard');
});

app.get('/logout', async (req, res) => {
  const cookies = parseCookies(req);
  const sid = cookies['SEVAM_SID'];
  if (sid) sessions.delete(sid);
  res.setHeader('Set-Cookie', 'SEVAM_SID=; Path=/; Max-Age=0');
  res.redirect('/login');
});
app.get('/logout.php', (req, res) => res.redirect('/logout'));

// Provider Routes
app.get('/provider/dashboard', (req, res) => {
  const user = getSessionUser(req);
  if (!user || user.role !== 'provider') return res.redirect('/login');
  res.send(renderProviderDashboard(user));
});
app.get('/provider/dashboard.php', (req, res) => res.redirect('/provider/dashboard'));

app.get('/provider/add-food', (req, res) => {
  const user = getSessionUser(req);
  if (!user || user.role !== 'provider') return res.redirect('/login');
  res.send(renderProviderAddFood(user));
});
app.get('/provider/add-food.php', (req, res) => res.redirect('/provider/add-food'));

app.post('/provider/add-food', async (req, res) => {
  const user = getSessionUser(req);
  if (!user || user.role !== 'provider') return res.redirect('/login');
  const provider = db.providers.find(p => p.user_id === user.id) || db.providers[0];
  const { food_name, category_id, food_type, quantity, quantity_unit, available_date, expiry_time, pickup_info } = req.body;

  const newListing = {
    id: db.nextListingId++,
    provider_id: provider.id,
    category_id: Number(category_id) || 1,
    food_name,
    food_type: food_type === 'Non-Veg' ? 'Non-Veg' : 'Veg',
    quantity: Number(quantity) || 10,
    available_quantity: Number(quantity) || 10,
    quantity_unit: quantity_unit || 'portions',
    price: 0,
    available_date: available_date || new Date().toISOString().split('T')[0],
    available_start_time: '18:00',
    available_end_time: '21:00',
    expiry_date: available_date || new Date().toISOString().split('T')[0],
    expiry_time: expiry_time || '22:00',
    pickup_info: pickup_info || 'Front counter',
    status: 'Available' as const,
    created_at: new Date().toISOString()
  };
  db.listings.unshift(newListing);
  await syncToSupabase('food_listings', 'POST', newListing);
  await recordSupabaseAction('food_listed', { food_name, quantity }, 'food_listing', newListing.id);
  res.redirect('/provider/dashboard');
});

app.post('/provider/toggle-listing', async (req, res) => {
  const id = Number(req.body.id);
  const listing = db.listings.find(l => l.id === id);
  if (listing) {
    listing.status = listing.status === 'Available' ? 'Unavailable' : 'Available';
    await syncToSupabase(`food_listings?id=eq.${id}`, 'PATCH', { status: listing.status });
  }
  res.redirect('/provider/dashboard');
});

// Group Routes
app.get('/group/dashboard', (req, res) => {
  const user = getSessionUser(req);
  if (!user || user.role !== 'group') return res.redirect('/login');
  res.send(renderGroupDashboard(user));
});
app.get('/group/dashboard.php', (req, res) => res.redirect('/group/dashboard'));
app.get('/group/food-availability', (req, res) => res.redirect('/'));

// Admin Routes
app.get('/admin/dashboard', (req, res) => {
  const user = getSessionUser(req);
  if (!user || user.role !== 'admin') return res.redirect('/login');
  res.send(renderAdminDashboard(user));
});
app.get('/admin/dashboard.php', (req, res) => res.redirect('/admin/dashboard'));
app.get('/admin/users', (req, res) => res.redirect('/admin/dashboard'));
app.get('/admin/foods', (req, res) => res.redirect('/admin/dashboard'));
app.get('/admin/requests', (req, res) => res.redirect('/admin/dashboard'));
app.get('/admin/categories', (req, res) => res.redirect('/admin/dashboard'));
app.get('/admin/messages', (req, res) => res.redirect('/admin/dashboard'));

app.listen(PORT, '0.0.0.0', () => {
  console.log(`Sevam Application Server listening on http://0.0.0.0:${PORT}`);
});
