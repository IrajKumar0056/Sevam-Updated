import { db } from './store.ts';
import type { FoodListing, User } from './store.ts';
import { renderLayout } from './views.ts';

export function renderProviderDashboard(user: User): string {
  const provider = db.providers.find(p => p.user_id === user.id) || db.providers[0];
  const myListings = db.listings.filter(l => l.provider_id === provider?.id);
  const myRequests = db.requests.filter(r => myListings.some(l => l.id === r.food_id));

  const content = `
    <div class="container" style="padding:2.5rem 1rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
            <div>
                <h1 style="font-size:2rem; font-weight:800; margin:0;">${provider?.business_name || 'Kitchen Dashboard'}</h1>
                <p style="color:var(--color-text-muted);">Manage surplus food donations and coordination</p>
            </div>
            <a href="/provider/add-food" class="btn btn-primary">+ List New Surplus Food</a>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem; margin-bottom:2.5rem;">
            <div class="data-card" style="padding:1.5rem; text-align:center;">
                <div style="font-size:2rem; font-weight:800; color:var(--color-primary);">${myListings.length}</div>
                <div style="color:var(--color-text-muted); font-size:0.9rem;">Total Food Listings</div>
            </div>
            <div class="data-card" style="padding:1.5rem; text-align:center;">
                <div style="font-size:2rem; font-weight:800; color:var(--color-warning);">${myRequests.filter(r => r.status === 'Pending').length}</div>
                <div style="color:var(--color-text-muted); font-size:0.9rem;">Pending NGO Claims</div>
            </div>
            <div class="data-card" style="padding:1.5rem; text-align:center;">
                <div style="font-size:2rem; font-weight:800; color:var(--color-success);">${myRequests.filter(r => r.status === 'Completed' || r.status === 'Accepted').length}</div>
                <div style="color:var(--color-text-muted); font-size:0.9rem;">Fulfilled Redistributions</div>
            </div>
        </div>

        <div class="data-card" style="padding:1.5rem;">
            <h3 style="font-size:1.2rem; font-weight:700; margin-bottom:1rem;">Recent Listings</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Food Item</th>
                            <th>Available Qty</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${myListings.map(l => `
                            <tr>
                                <td><strong>${l.food_name}</strong></td>
                                <td>${l.available_quantity} ${l.quantity_unit}</td>
                                <td>${l.expiry_date} ${l.expiry_time}</td>
                                <td><span class="badge" style="background:${l.status === 'Available' ? '#dcfce7; color:#15803d;' : '#f1f5f9; color:#475569;'}">${l.status}</span></td>
                                <td>
                                    <form action="/provider/toggle-listing" method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="${l.id}">
                                        <button type="submit" class="btn btn-secondary btn-sm">${l.status === 'Available' ? 'Mark Claimed' : 'Make Available'}</button>
                                    </form>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  `;
  return renderLayout('Provider Dashboard', user, content, 'dashboard');
}

export function renderProviderAddFood(user: User): string {
  const content = `
    <div class="container" style="padding:2.5rem 1rem; max-width:650px;">
        <h1 style="font-size:2rem; font-weight:800; margin-bottom:1.5rem;">Add Surplus Food Listing</h1>
        <div class="data-card" style="padding:2rem;">
            <form action="/provider/add-food" method="POST">
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">Food Item Title</label>
                    <input type="text" name="food_name" class="form-input" placeholder="e.g. Steamed Rice & Dal Tadka" required>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
                    <div>
                        <label class="form-label">Food Category</label>
                        <select name="category_id" class="form-input" required>
                            ${db.categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Type</label>
                        <select name="food_type" class="form-input" required>
                            <option value="Veg">Vegetarian</option>
                            <option value="Non-Veg">Non-Vegetarian</option>
                        </select>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
                    <div>
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-input" min="1" value="25" required>
                    </div>
                    <div>
                        <label class="form-label">Unit</label>
                        <input type="text" name="quantity_unit" class="form-input" value="portions" required>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
                    <div>
                        <label class="form-label">Available Date</label>
                        <input type="date" name="available_date" class="form-input" value="${new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div>
                        <label class="form-label">Expiry Time</label>
                        <input type="time" name="expiry_time" class="form-input" value="22:00" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label class="form-label">Pickup Location & Instructions</label>
                    <textarea name="pickup_info" rows="2" class="form-input" placeholder="Kitchen counter, ask for Chef Ramesh" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Publish Food Listing</button>
            </form>
        </div>
    </div>
  `;
  return renderLayout('Add Food', user, content, 'add-food');
}

export function renderGroupDashboard(user: User): string {
  const group = db.groups.find(g => g.user_id === user.id) || db.groups[0];
  const myRequests = db.requests.filter(r => r.group_id === group?.id);

  const content = `
    <div class="container" style="padding:2.5rem 1rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
            <div>
                <h1 style="font-size:2rem; font-weight:800; margin:0;">${group?.group_name || 'Volunteer Dashboard'}</h1>
                <p style="color:var(--color-text-muted);">Redistributing surplus food to local shelters & communities</p>
            </div>
            <a href="/group/food-availability" class="btn btn-primary">Browse Available Food</a>
        </div>

        <div class="data-card" style="padding:1.5rem; margin-bottom:2rem;">
            <h3 style="font-size:1.2rem; font-weight:700; margin-bottom:1rem;">Active Food Redistribution Claims</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Food Item</th>
                            <th>Claimed Quantity</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${myRequests.map(r => {
                          const listing = db.listings.find(l => l.id === r.food_id);
                          return `
                            <tr>
                                <td><strong>${listing?.food_name || 'Food item'}</strong></td>
                                <td>${r.requested_quantity} portions</td>
                                <td>${r.requested_date}</td>
                                <td><span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:600;">${r.status}</span></td>
                            </tr>
                          `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  `;
  return renderLayout('Group Dashboard', user, content, 'dashboard');
}

export function renderAdminDashboard(user: User): string {
  const content = `
    <div class="container" style="padding:2.5rem 1rem;">
        <h1 style="font-size:2rem; font-weight:800; margin-bottom:1rem;">Admin Control Center</h1>
        <p style="color:var(--color-text-muted); margin-bottom:2rem;">Platform telemetry, verification, and live activity overview</p>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1.25rem; margin-bottom:2rem;">
            <div class="data-card" style="padding:1.25rem; text-align:center;">
                <div style="font-size:1.8rem; font-weight:800; color:var(--color-primary);">${db.users.length}</div>
                <div style="font-size:0.85rem; color:var(--color-text-muted);">Total Users</div>
            </div>
            <div class="data-card" style="padding:1.25rem; text-align:center;">
                <div style="font-size:1.8rem; font-weight:800; color:var(--color-primary);">${db.providers.length}</div>
                <div style="font-size:0.85rem; color:var(--color-text-muted);">Food Providers</div>
            </div>
            <div class="data-card" style="padding:1.25rem; text-align:center;">
                <div style="font-size:1.8rem; font-weight:800; color:var(--color-primary);">${db.groups.length}</div>
                <div style="font-size:0.85rem; color:var(--color-text-muted);">Social Groups</div>
            </div>
            <div class="data-card" style="padding:1.25rem; text-align:center;">
                <div style="font-size:1.8rem; font-weight:800; color:var(--color-primary);">${db.listings.length}</div>
                <div style="font-size:0.85rem; color:var(--color-text-muted);">Listings</div>
            </div>
            <div class="data-card" style="padding:1.25rem; text-align:center;">
                <div style="font-size:1.8rem; font-weight:800; color:var(--color-primary);">${db.requests.length}</div>
                <div style="font-size:0.85rem; color:var(--color-text-muted);">Requests</div>
            </div>
        </div>

        <div class="data-card" style="padding:1.5rem;">
            <h3 style="font-size:1.2rem; font-weight:700; margin-bottom:1rem;">Recent Contact Inquiries</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${db.messages.map(m => `
                            <tr>
                                <td><strong>${m.name}</strong></td>
                                <td>${m.email}</td>
                                <td>${m.subject}</td>
                                <td>${m.message}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  `;
  return renderLayout('Admin Dashboard', user, content, 'dashboard');
}
