import bcrypt from 'bcryptjs';

export interface User {
  id: number;
  username: string;
  email: string;
  password: string;
  role: 'admin' | 'provider' | 'group';
  status: 'active' | 'inactive';
  created_at: string;
}

export interface FoodCategory {
  id: number;
  name: string;
  description: string;
  status: 'active' | 'inactive';
}

export interface FoodProvider {
  id: number;
  user_id: number;
  business_name: string;
  owner_name: string;
  phone: string;
  address: string;
  city: string;
  state: string;
  business_type: string;
  fssai_status: string;
  fssai_number: string;
}

export interface SocialWorkingGroup {
  id: number;
  user_id: number;
  group_name: string;
  representative_name: string;
  phone: string;
  address: string;
  city: string;
  state: string;
  organization_type: string;
  darpan_id: string;
}

export interface FoodListing {
  id: number;
  provider_id: number;
  category_id: number;
  food_name: string;
  food_type: 'Veg' | 'Non-Veg';
  quantity: number;
  available_quantity: number;
  quantity_unit: string;
  price: number;
  available_date: string;
  available_start_time: string;
  available_end_time: string;
  expiry_date: string;
  expiry_time: string;
  pickup_info: string;
  status: 'Available' | 'Unavailable' | 'Reserved';
  created_at: string;
}

export interface FoodRequest {
  id: number;
  food_id: number;
  group_id: number;
  requested_quantity: number;
  requested_date: string;
  requested_time: string;
  status: 'Pending' | 'Accepted' | 'Rejected' | 'Completed';
  message: string;
  created_at: string;
}

export interface ContactMessage {
  id: number;
  name: string;
  email: string;
  phone: string;
  subject: string;
  message: string;
  created_at: string;
}

const SUPABASE_URL = process.env.SUPABASE_URL || 'https://vvltacibwvrwtfhyesur.supabase.co';
const SUPABASE_API_KEY = process.env.SUPABASE_API_KEY || 'sb_publishable_uPIh-o3lSFGmjZsh7Z3cMA_hvwAjvRy';

export async function syncToSupabase(endpoint: string, method: string = 'POST', body?: any) {
  try {
    const res = await fetch(`${SUPABASE_URL}/rest/v1/${endpoint}`, {
      method,
      headers: {
        'apikey': SUPABASE_API_KEY,
        'Authorization': `Bearer ${SUPABASE_API_KEY}`,
        'Content-Type': 'application/json',
        'Prefer': method === 'POST' ? 'return=representation' : 'return=minimal'
      },
      body: body ? JSON.stringify(body) : undefined
    });
    return res.status;
  } catch (err) {
    return 500;
  }
}

export async function recordSupabaseAction(action: string, details: any, entityType = 'general', entityId = '') {
  return syncToSupabase('actions', 'POST', {
    action,
    entity_type: entityType,
    entity_id: String(entityId),
    details,
    created_at: new Date().toISOString()
  });
}

// In-Memory Database Store seeded with original dataset
export class DatabaseStore {
  users: User[] = [
    {
      id: 1,
      username: 'admin',
      email: 'admin@sevam.org',
      password: bcrypt.hashSync('admin123', 10),
      role: 'admin',
      status: 'active',
      created_at: '2026-01-01 10:00:00'
    },
    {
      id: 2,
      username: 'annapurna_kitchen',
      email: 'info@annapurnakitchen.com',
      password: bcrypt.hashSync('provider123', 10),
      role: 'provider',
      status: 'active',
      created_at: '2026-01-02 11:00:00'
    },
    {
      id: 3,
      username: 'royal_caterers',
      email: 'contact@royalcaterers.in',
      password: bcrypt.hashSync('provider123', 10),
      role: 'provider',
      status: 'active',
      created_at: '2026-01-03 09:30:00'
    },
    {
      id: 4,
      username: 'hope_foundation',
      email: 'connect@hopefoundation.org',
      password: bcrypt.hashSync('group123', 10),
      role: 'group',
      status: 'active',
      created_at: '2026-01-04 14:15:00'
    },
    {
      id: 5,
      username: 'seva_youth_circle',
      email: 'team@sevayouth.org',
      password: bcrypt.hashSync('group123', 10),
      role: 'group',
      status: 'active',
      created_at: '2026-01-05 16:00:00'
    }
  ];

  categories: FoodCategory[] = [
    { id: 1, name: 'Cooked Meals', description: 'Freshly cooked rice, curries, chapati, full lunch/dinner meals', status: 'active' },
    { id: 2, name: 'Bakery & Bread', description: 'Breads, buns, muffins, and baked goods', status: 'active' },
    { id: 3, name: 'Fresh Produce', description: 'Vegetables and farm harvest surplus', status: 'active' },
    { id: 4, name: 'Packaged Food', description: 'Sealed snacks, biscuits, cereals, and dry goods', status: 'active' },
    { id: 5, name: 'Dairy & Beverages', description: 'Milk packets, curd, paneer, and juices', status: 'active' },
    { id: 6, name: 'Fruits', description: 'Apples, bananas, citrus and seasonal fresh fruits', status: 'active' }
  ];

  providers: FoodProvider[] = [
    {
      id: 1,
      user_id: 2,
      business_name: 'Annapurna Community Kitchen',
      owner_name: 'Ramesh Sharma',
      phone: '+91 98765 43210',
      address: '14 MG Road, Near Central Bus Stand',
      city: 'Indore',
      state: 'Madhya Pradesh',
      business_type: 'Community Kitchen / Restaurant',
      fssai_status: 'verified',
      fssai_number: '11422850000123'
    },
    {
      id: 2,
      user_id: 3,
      business_name: 'Royal Caterers & Banquet',
      owner_name: 'Vikram Singh',
      phone: '+91 91234 56789',
      address: 'Plot 45, Sector 12, Ring Road',
      city: 'Bhopal',
      state: 'Madhya Pradesh',
      business_type: 'Catering & Event Services',
      fssai_status: 'verified',
      fssai_number: '11423850000456'
    }
  ];

  groups: SocialWorkingGroup[] = [
    {
      id: 1,
      user_id: 4,
      group_name: 'Hope For All Foundation',
      representative_name: 'Dr. Anita Desai',
      phone: '+91 97890 12345',
      address: '77 Shanti Nagar, Near Railway Station',
      city: 'Indore',
      state: 'Madhya Pradesh',
      organization_type: 'Registered NGO',
      darpan_id: 'MP/2021/0291823'
    },
    {
      id: 2,
      user_id: 5,
      group_name: 'Seva Youth Circle',
      representative_name: 'Karan Verma',
      phone: '+91 98111 22233',
      address: '12 Vivekananda Marg',
      city: 'Bhopal',
      state: 'Madhya Pradesh',
      organization_type: 'Volunteer Group',
      darpan_id: 'MP/2022/0312004'
    }
  ];

  listings: FoodListing[] = [
    {
      id: 1,
      provider_id: 1,
      category_id: 1,
      food_name: 'Nutritious Veg Biryani with Raita',
      food_type: 'Veg',
      quantity: 50,
      available_quantity: 35,
      quantity_unit: 'portions',
      price: 0,
      available_date: '2026-10-01',
      available_start_time: '19:00',
      available_end_time: '21:30',
      expiry_date: '2026-10-01',
      expiry_time: '23:00',
      pickup_info: 'Kitchen back gate, ask for Chef Ramesh',
      status: 'Available',
      created_at: '2026-09-28 08:00:00'
    },
    {
      id: 2,
      provider_id: 1,
      category_id: 1,
      food_name: 'Fresh Chapati & Dal Makhani',
      food_type: 'Veg',
      quantity: 80,
      available_quantity: 80,
      quantity_unit: 'portions',
      price: 0,
      available_date: '2026-10-01',
      available_start_time: '18:30',
      available_end_time: '21:00',
      expiry_date: '2026-10-01',
      expiry_time: '22:30',
      pickup_info: 'Main counter, clean containers required',
      status: 'Available',
      created_at: '2026-09-28 08:30:00'
    },
    {
      id: 3,
      provider_id: 2,
      category_id: 2,
      food_name: 'Assorted Bakery Bread & Rolls',
      food_type: 'Veg',
      quantity: 40,
      available_quantity: 40,
      quantity_unit: 'kg',
      price: 0,
      available_date: '2026-10-01',
      available_start_time: '17:00',
      available_end_time: '20:00',
      expiry_date: '2026-10-02',
      expiry_time: '12:00',
      pickup_info: 'Loading bay entrance 3',
      status: 'Available',
      created_at: '2026-09-28 09:00:00'
    }
  ];

  requests: FoodRequest[] = [
    {
      id: 1,
      food_id: 1,
      group_id: 1,
      requested_quantity: 15,
      requested_date: '2026-10-01',
      requested_time: '19:30',
      status: 'Accepted',
      message: 'Distributing to night shelter at Rajwada',
      created_at: '2026-09-28 09:30:00'
    }
  ];

  messages: ContactMessage[] = [
    {
      id: 1,
      name: 'Sunil Mehta',
      email: 'sunil@hotelgrand.com',
      phone: '+91 99887 76655',
      subject: 'Partnering our hotel banquet surplus',
      message: 'We host weekend weddings and often have 100+ meal surplus. How can we onboard?',
      created_at: '2026-09-28 10:00:00'
    }
  ];

  nextUserId = 10;
  nextListingId = 10;
  nextRequestId = 10;
  nextMsgId = 10;
  nextCatId = 10;
}

export const db = new DatabaseStore();
