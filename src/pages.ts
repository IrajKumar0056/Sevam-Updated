import { db } from './store.ts';
import type { FoodListing, User } from './store.ts';
import { renderLayout } from './views.ts';

export function renderHomePage(user: User | null, query = '', category = ''): string {
  let listings = db.listings.filter(l => l.status === 'Available');
  if (query) {
    listings = listings.filter(l => l.food_name.toLowerCase().includes(query.toLowerCase()));
  }
  if (category) {
    const catId = Number(category);
    if (catId) listings = listings.filter(l => l.category_id === catId);
  }

  const categoryChips = db.categories.map(c => `
    <a href="/?cat=${c.id}${query ? `&q=${encodeURIComponent(query)}` : ''}" 
       class="btn btn-sm ${category === String(c.id) ? 'btn-primary' : 'btn-secondary'}" 
       style="border-radius:9999px;">
       ${c.name}
    </a>
  `).join('');

  const listingCards = listings.length === 0 ? `
    <div style="grid-column: 1 / -1; text-align:center; padding:3rem; background:#fff; border-radius:12px; border:1px dashed var(--color-border);">
        <p style="color:var(--color-text-muted); font-size:1.1rem; margin-bottom:1rem;">No surplus food listings match your criteria.</p>
        <a href="/" class="btn btn-secondary btn-sm">Clear Filters</a>
    </div>
  ` : listings.map(l => {
    const prov = db.providers.find(p => p.id === l.provider_id);
    const cat = db.categories.find(c => c.id === l.category_id);
    return `
      <div class="data-card" style="padding:1.5rem; display:flex; flex-direction:column; justify-content:space-between;">
          <div>
              <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                  <span class="badge" style="background:#e8f5e9; color:#1b4332; font-weight:600;">${cat?.name || 'Meal'}</span>
                  <span class="badge" style="background:${l.food_type === 'Veg' ? '#dcfce7; color:#15803d;' : '#fee2e2; color:#b91c1c;'}">${l.food_type}</span>
              </div>
              <h3 style="font-size:1.2rem; font-weight:700; margin-bottom:0.5rem; color:var(--color-text-main);">${l.food_name}</h3>
              <p style="font-size:0.9rem; color:var(--color-text-muted); margin-bottom:1rem;">
                  Provided by <strong>${prov?.business_name || 'Verified Kitchen'}</strong><br>
                  📍 ${prov?.city || 'Indore'}, ${prov?.state || 'MP'}
              </p>
              <div style="background:var(--color-surface-subtle); padding:0.75rem; border-radius:8px; margin-bottom:1rem; font-size:0.85rem;">
                  <div>📦 <strong>Available Quantity:</strong> ${l.available_quantity} ${l.quantity_unit}</div>
                  <div>⏰ <strong>Pickup Window:</strong> ${l.available_start_time} - ${l.available_end_time}</div>
                  <div>⏳ <strong>Best Before:</strong> ${l.expiry_date} ${l.expiry_time}</div>
              </div>
          </div>
          <div>
              ${user && user.role === 'group' 
                ? `<a href="/group/food-availability?claim=${l.id}" class="btn btn-primary btn-block">Claim for Distribution</a>`
                : `<a href="/login" class="btn btn-secondary btn-block">Sign in as NGO to Claim</a>`}
          </div>
      </div>
    `;
  }).join('');

  const content = `
    <section class="hero-section" style="background:linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); color:#fff; padding:4rem 1rem; text-align:center;">
        <div class="container" style="max-width:800px;">
            <span class="badge" style="background:rgba(255,255,255,0.2); color:#fff; margin-bottom:1rem;">Direct Food Redistribution</span>
            <h1 style="font-size:2.8rem; font-weight:800; line-height:1.2; margin-bottom:1rem;">Transform Surplus Meals into Community Hope</h1>
            <p style="font-size:1.15rem; color:rgba(255,255,255,0.85); margin-bottom:2rem;">
                Connecting verified restaurants, caterers, and food businesses directly with local NGOs and volunteers.
            </p>
            <div style="display:flex; justify-content:center; gap:1rem; flex-wrap:wrap;">
                <a href="/register?role=provider" class="btn" style="background:#fff; color:#1b4332; font-weight:700;">Donate Surplus Food</a>
                <a href="/register?role=group" class="btn" style="background:#40916c; color:#fff; font-weight:700;">Join as Volunteer Group</a>
            </div>
        </div>
    </section>

    <section class="container" style="padding:3.5rem 1rem;">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
            <div>
                <h2 style="font-size:1.75rem; font-weight:800;">Fresh Surplus Available Right Now</h2>
                <p style="color:var(--color-text-muted);">Verified edible meals ready for immediate redistribution</p>
            </div>
            <form action="/" method="GET" style="display:flex; gap:0.5rem; max-width:400px; width:100%;">
                <input type="text" name="q" value="${query}" placeholder="Search food items..." class="form-input" style="padding:0.5rem 1rem;">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </form>
        </div>

        <div style="display:flex; gap:0.5rem; margin-bottom:2rem; overflow-x:auto; padding-bottom:0.5rem;">
            <a href="/${query ? `?q=${encodeURIComponent(query)}` : ''}" class="btn btn-sm ${!category ? 'btn-primary' : 'btn-secondary'}" style="border-radius:9999px;">All Categories</a>
            ${categoryChips}
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1.5rem;">
            ${listingCards}
        </div>
    </section>
  `;

  return renderLayout('Home', user, content, 'home');
}

export function renderAboutPage(user: User | null): string {
  const content = `
    <div class="container" style="padding:4rem 1rem; max-width:850px;">
        <h1 style="font-size:2.5rem; font-weight:800; margin-bottom:1rem; color:var(--color-primary);">About Sevam</h1>
        <p style="font-size:1.15rem; color:var(--color-text-muted); line-height:1.7; margin-bottom:2rem;">
            Sevam was founded with a singular conviction: good, freshly prepared food should never end up in landfills while families and shelter residents in our neighborhoods go hungry.
        </p>
        <div class="data-card" style="padding:2rem; margin-bottom:2rem;">
            <h3 style="font-size:1.3rem; font-weight:700; margin-bottom:0.75rem;">Our Mission & Operating Model</h3>
            <p style="color:var(--color-text-muted); line-height:1.6;">
                Every evening, commercial kitchens, marriage banquets, and caterers prepare surplus meals with strict hygiene standards. Sevam provides a digital bridge allowing kitchen managers to publish available portions within 30 seconds. Local registered NGOs claim the food and coordinate volunteer drivers for prompt pickup and immediate neighborhood distribution.
            </p>
        </div>
    </div>
  `;
  return renderLayout('About Us', user, content, 'about');
}

export function renderContactPage(user: User | null, message = '', error = ''): string {
  const content = `
    <div class="container" style="padding:4rem 1rem; max-width:700px;">
        <h1 style="font-size:2.5rem; font-weight:800; margin-bottom:0.5rem; color:var(--color-primary);">Get in Touch</h1>
        <p style="color:var(--color-text-muted); margin-bottom:2rem;">Questions about onboarding or corporate food donation? We are here to help.</p>

        ${message ? `<div class="alert alert-success" style="margin-bottom:1.5rem;">${message}</div>` : ''}
        ${error ? `<div class="alert alert-danger" style="margin-bottom:1.5rem;">${error}</div>` : ''}

        <div class="data-card" style="padding:2rem;">
            <form action="/contact" method="POST">
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-input">
                </div>
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-input" required>
                </div>
                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label class="form-label">Message</label>
                    <textarea name="message" rows="4" class="form-input" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send Message</button>
            </form>
        </div>
    </div>
  `;
  return renderLayout('Contact Us', user, content, 'contact');
}

export function renderLoginPage(user: User | null, error = ''): string {
  const content = `
    <div class="container" style="padding:4rem 1rem; max-width:440px;">
        <div class="data-card" style="padding:2.25rem;">
            <div style="text-align:center; margin-bottom:1.75rem;">
                <span class="brand-mark" style="width:48px; height:48px; font-size:1.5rem; margin:0 auto 1rem auto;">S</span>
                <h1 style="font-size:1.75rem; font-weight:800; margin:0;">Welcome Back</h1>
                <p style="color:var(--color-text-muted); font-size:0.9rem; margin-top:0.25rem;">Sign in to your Sevam Portal</p>
            </div>

            ${error ? `<div class="alert alert-danger" style="margin-bottom:1.25rem; font-size:0.875rem;">${error}</div>` : ''}

            <form action="/login" method="POST">
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label" for="identity">Username or Email</label>
                    <input type="text" id="identity" name="identity" class="form-input" placeholder="e.g. annapurna_kitchen or admin" required autofocus>
                </div>
                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>
        </div>
        <div style="text-align:center; margin-top:1.5rem; font-size:0.9rem; color:var(--color-text-muted);">
            Don't have an account yet? <a href="/register" style="color:var(--color-primary); font-weight:600;">Register here</a>
        </div>
    </div>
  `;
  return renderLayout('Sign In', user, content);
}

export function renderRegisterPage(user: User | null, selectedRole = 'provider', error = ''): string {
  const content = `
    <div class="container" style="padding:4rem 1rem; max-width:540px;">
        <div class="data-card" style="padding:2.25rem;">
            <div style="text-align:center; margin-bottom:1.75rem;">
                <h1 style="font-size:1.75rem; font-weight:800; margin:0;">Create an Account</h1>
                <p style="color:var(--color-text-muted); font-size:0.9rem; margin-top:0.25rem;">Join the Sevam redistribution network</p>
            </div>

            ${error ? `<div class="alert alert-danger" style="margin-bottom:1.25rem;">${error}</div>` : ''}

            <form action="/register" method="POST">
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">I am registering as a:</label>
                    <select name="role" class="form-input" required>
                        <option value="provider" ${selectedRole === 'provider' ? 'selected' : ''}>Food Provider (Restaurant, Caterer, Kitchen)</option>
                        <option value="group" ${selectedRole === 'group' ? 'selected' : ''}>Social Working Group / NGO</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">Organization / Business Name</label>
                    <input type="text" name="org_name" class="form-input" placeholder="e.g. Sunrise Community Kitchen" required>
                </div>
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">Contact Person Name</label>
                    <input type="text" name="contact_name" class="form-input" placeholder="e.g. Rajesh Kumar" required>
                </div>
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" placeholder="e.g. sunrise_kitchen" required>
                </div>
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" placeholder="contact@example.org" required>
                </div>
                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Complete Registration</button>
            </form>
        </div>
        <div style="text-align:center; margin-top:1.5rem; font-size:0.9rem; color:var(--color-text-muted);">
            Already registered? <a href="/login" style="color:var(--color-primary); font-weight:600;">Sign in here</a>
        </div>
    </div>
  `;
  return renderLayout('Register', user, content);
}
