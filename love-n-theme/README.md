# Love, N: WordPress theme

A warm, nostalgic block theme for Namitha Raveendranathan's portfolio and shop.

Home page order: hero video → Love, N Art Mail Club → shop cards (artworks, postcards, bookmarks, stickers) → portfolio → features → about.

## What happens when it's switched on
The first time the theme is active it creates:
- the pages **Home** (front page), **Work**, **Features**, **Love, N Art Mail Club**, **Contact**, and **Shipping / Refund policy** and **Terms of service** pages for the footer links
- with WooCommerce active: the shop categories **Art prints, Bookmarks, Stickers, Postcards, Art Mail Club** and 8 starter products

Products are created once. After a theme update, a generated page is refreshed with the new design **only if it hasn't been edited**. Edited pages are never overwritten.

## Replace before launch
- Every image in `assets/img` and the hero video in `assets/video` are labelled placeholders. On the Home page, select the hero and use **Replace** to upload a studio video.
- The pink shipping bar at the top is in the Header template part (Appearance → Editor → Patterns → Header). Add the real shipping note there, plus a logo with the Site Logo block.
- Social links in the footer point to `#`. Add the real profile links.
- Text in `[square brackets]` is a placeholder.
- **Product prices are placeholders** (in the store's currency). Check them under Products.
- Connect a payment method under WooCommerce → Settings → Payments.

## Forms
- Contact form: `[ln_contact]`
- Email sign-up: `[ln_signup source="newsletter" button="Sign me up"]`
- Mail Club plan picker (photo, plan pills and a Join button; plans are products in the "Art Mail Club" category, named like `Mail Club · 3 months`): `[ln_mail_club_plans]`

Messages and sign-ups are emailed to the site admin and also saved under **Love, N** in the WordPress admin, where subscribers can be exported as a CSV.

## Cart drawer and local prices
- The header cart icon opens a cart drawer built by the theme (`inc/cart.php`, `assets/js/cart-drawer.js`). It uses the WooCommerce Store API, so it works while the store is in "coming soon" mode (WooCommerce's own mini-cart block is hidden in that mode).
- On the Mail Club page, International plans also show an approximate price in the visitor's currency (guessed from their device's time zone; they can change it). Payment is still in INR. Rates come from open.er-api.com (fallback: frankfurter.app), refreshed every 12 hours. If neither can be reached and no earlier rates are saved, the approximate line is simply not shown.

## Shop page
The Shop page uses the Work page layout: a "Shop all" tab plus one tab per product category (Artworks & prints, Postcards, Bookmarks, Stickers, then any new category once it has products), all on one page. The layout comes from `templates/archive-product.html` and the `[ln_shop]` shortcode (`inc/shop.php`). Category links like `/product-category/postcards/` open the matching tab (`/shop/#postcards`); the Art Mail Club category goes to the Mail Club page.
