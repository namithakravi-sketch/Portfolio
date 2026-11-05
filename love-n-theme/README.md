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
