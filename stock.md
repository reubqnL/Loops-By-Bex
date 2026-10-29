# LOOPS BY BEX — HOW TO ADD OR EDIT STOCK

---

## Step 1: Add the product photo

* Crop the photo nicely (**square works best**).
* Save it as a `.webp` or `.jpg` with a simple lowercase name and dashes (no spaces), e.g., `strawberry-cow.webp`.
* Put the image file inside the `assets/` folder.

---

## Step 2: Choose how you want to list it

*(Note: Money is always written in **pence with no decimal point** — so £4.50 is `450`, £12.00 is `1200`, £18.50 is `1850`.)*

### Option A — Quickest way (Editing code in PhpStorm, no database needed)
*(Works as long as the products table in phpMyAdmin is left empty.)*

1. Open `includes/catalog.php`.
2. Copy or edit one of the product blocks inside `default_placeholder_products()`:
   * `'id'` → Next number up (`1`, `2`, `3`...)
   * `'slug'` → URL name with dashes, no spaces (e.g., `'strawberry-cow'`)
   * `'name'` → Title shown on the card (e.g., `'Crochet Strawberry Cow'`)
   * `'category'` → `'Plushies'`, `'Amigurumi'`, `'Accessories'`, or `'Custom'`
   * `'short_description'` → 1 short sentence shown on shop cards
   * `'description'` → Full description (size, yarn type, care instructions)
   * `'price_pence'` → Price in pence (e.g., `1250` for £12.50)
   * `'image_path'` → `'/assets/strawberry-cow.webp'`
   * `'stock_qty'` → How many are available (`0` marks it Sold Out)
   * `'is_featured_main'` → `1` to show in the main 4-card New Arrivals grid on the Home page (`0` otherwise)
   * `'is_featured_sidebar'` → `1` to show in the right-hand sidebar on the Home page (`0` otherwise)
3. **Postage price:** Open `includes/cart.php` around line 99 and change `'shipping_pence' => 0` to your postage cost in pence (e.g., `350` for £3.50 UK delivery).
4. Upload `assets/`, `includes/catalog.php`, and `includes/cart.php` to IONOS. Every page on the site updates automatically!

### Option B — Automatic stock countdown (Using phpMyAdmin for 1-of-1 items)
*(Best once real sales start so two people can't buy the same one-off plushie at once.)*

1. Upload the photo to `assets/` on IONOS.
2. Open **IONOS** → **Databases** → **phpMyAdmin** → **products table** → **Insert**.
3. Fill in the same fields (`slug`, `name`, `category`, `price_pence`, `image_path`, `stock_qty`), tick `is_active = 1`, and click **Go**.
4. Whenever someone buys one, the website automatically reduces `stock_qty` by 1 and switches it to **Out of stock** when it hits 0.

---

## Master Checklist: Automated vs What YOU Personally Need to Change

### 1. What is 100% Automated (You never touch this)
* **Basket & totals:** Adding/removing items, basket badge count in the header, and server-side GBP totals.
* **Security:** Session cookies, CSRF tokens on every form, spam bot honeypots, IP-hashed rate limiting, SQL injection protection, and HTTP security headers.
* **Payments & stock countdown:** Redirecting shoppers to Stripe Hosted Checkout, verifying the Stripe webhook signature (`webhook-stripe.php`), marking the order as paid in MySQL, and automatically reducing `stock_qty` so sold-out items switch to **Out of stock**.
* **Order lookup (`/account`):** Customers can look up their own order status using their `LBB-...` order reference + email.
* **Contact form emails:** Saving messages to `contact_messages` and emailing `CONTACT_EMAIL` when someone submits `/contact`.

### 2. `.env` Variables YOU Personally Need to Set (Live on IONOS)
Your `.env` file on IONOS is separate from GitHub (it never gets overwritten by Git). Here is every variable in it and when to change it:

* `APP_ENV=production` → Keep as `production` on IONOS (`development` on your Mac).
* `APP_URL=https://loopsbybex.co.uk` → Change to your exact live domain (with `https://`, no trailing slash).
* `APP_LAUNCHING_MODE=launching` →
  * Set to `launching` right now on IONOS (visitors only see the waitlist page; shop pages redirect back to waitlist).
  * Set to `preview` if you want `/` to show the waitlist while letting you test `/shop`, `/cart`, etc.
  * Flip to `live` on launch day to open the full website to everyone!
* `DB_DRIVER=mysql`, `DB_HOST=...`, `DB_NAME=...`, `DB_USER=...`, `DB_PASS=...` → Your IONOS MySQL credentials (already in your IONOS `.env`).
* `CONTACT_EMAIL=loopsbybex@outlook.com` → Whichever inbox Bex/her mum want contact messages & footer/privacy email links to use.
* `INSTAGRAM_URL=https://www.instagram.com/bex2_011/` → Update if Bex changes her Instagram handle.
* `ETSY_SHOP_URL=` → Leave blank for now (hides the Etsy icon); paste her Etsy URL if she ever opens one and the footer link appears automatically.
* `STRIPE_PUBLISHABLE_KEY=pk_live_...`
* `STRIPE_SECRET_KEY=sk_live_...`
* `STRIPE_WEBHOOK_SECRET=whsec_...` → Paste these 3 from her mum's Stripe dashboard before setting real prices above £0.

### 3. Code & Content Files YOU Personally Need to Edit Before Launch
Right now the site intentionally says "Placeholder" in a few places so nobody mistakes £0 test items for real stock. Before flipping `APP_LAUNCHING_MODE=live`, swap these:

* **Products & Prices:** If you imported `database/schema.sql` into phpMyAdmin, edit (or delete and replace) the 10 placeholder rows inside the `products` table in phpMyAdmin (or empty that table if you prefer editing `includes/catalog.php` directly).
* **Delivery / Postage Cost (`includes/cart.php` line 99):** Change `$shippingPence = 0;` to Bex's UK postage price in pence (e.g., `$shippingPence = 350;` for £3.50).
* **Hero Slideshow & Maker Photo (`pages/index.php` & `pages/about.php`):**
  * Replace `/assets/placeholders/hero-plushies.svg`, `hero-story.svg`, `hero-custom.svg` at the top of `pages/index.php` (lines 19, 29, 39) with real photos of Bex's crochet.
  * Replace `/assets/placeholders/maker-placeholder.svg` in `pages/index.php` (line 193) and `pages/about.php` (line 16) with a photo/logo for Bex.
* **Remove "Placeholder" wording on 4 pages once real prices are live:**
  * `pages/shop.php` (line 35) — change the subtitle under `<h1>Shop</h1>` from "Placeholder product catalogue..." to Bex's real shop tagline.
  * `pages/product.php` (line 58) — change "More Handmade Placeholders" to "You Might Also Like".
  * `pages/cart.php` (lines 26 & 98) and `pages/checkout.php` (lines 294–298) — remove the pre-launch £0 placeholder note box.
  * `pages/blog.php` (lines 10–35) — swap the 3 placeholder blog posts in `$posts` with Bex's real posts whenever she writes them.
* **Your SEO Tags (Issue #2):** Add your `<meta name="description">`, Open Graph (`og:title`, `og:image`), and canonical tags inside `partials/header.php` when you do Issue #2.
* **SCSS compiling reminder:** Anytime you edit a `.scss` file in `style/`, recompile its `.css` file (or turn on PhpStorm's Sass File Watcher with `--no-source-map`) because browsers read `.css`, not `.scss`.

### 4. Two One-Time External Setups + What to Keep Track Of Ongoing
* **One-time in IONOS phpMyAdmin:** Import `database/schema.sql` into your IONOS database so the `products`, `orders`, `order_items`, `contact_messages`, and `rate_limits` tables exist alongside `waitlist`.
* **One-time in Stripe Dashboard (under her mum's name):**
  1. Go to **Developers** → **Webhooks** → **Add endpoint**.
  2. Set Endpoint URL to `https://loopsbybex.co.uk/webhook-stripe.php` (using your real domain).
  3. Select event `checkout.session.completed`, click **Add**, and copy the `whsec_...` Signing Secret into `.env`.
* **What to keep track of week-to-week once live:**
  * Watch Stripe app / email notifications for new paid orders → check orders in phpMyAdmin for the shipping address and post the parcel.
  * Watch `CONTACT_EMAIL` inbox for custom crochet enquiries.
  * On launch day, export waitlist from phpMyAdmin to email everyone who signed up (and delete anyone who asks to unsubscribe).

---