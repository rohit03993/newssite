# PHP + MySQL public site (shared hosting)

This file is the plan. Do not start the rebuild inside the live Naradmuni server. Copy this whole project folder somewhere else, then build the PHP site in that copy. The live site at thenaradmuni.com stays on the VPS with Node until the PHP copy is proven.

## What we are making

One website made of PHP and MySQL only. No Node program. No `npm run build`. No service that must stay running.

It must run on ordinary shared hosting (PHP + MySQL), such as a Hostinger website plan.

Readers must see the same design as the current public site: same header, same homepage blocks, same article page, same colours, same menu.

A story published in the admin must show on the site straight away. The pages must open fast for daily reading.

The admin panel that already exists in `public_html/admin/` stays. It is already PHP. We do not throw it away and write a new admin.

## What we are not doing

- Do not upload today’s Next.js site (`web/`) onto shared hosting and expect it to run. That part needs Node.
- Do not freeze the site into HTML files. A newspaper cannot wait for a full rebuild after every story.
- Do not change article or category addresses. Google already uses them.
- Do not change image folder paths.
- Do not add a second login. One login, one dashboard.
- Do not change the live `config.php` on the VPS while experimenting. The copy has its own config.

## Addresses that must stay the same

| What | Address |
|---|---|
| Home | `/` |
| Article | `/news/{newsurl}` — the exact value already stored in MySQL |
| Category | `/category/{cat_url}` — the exact value already stored in MySQL |
| Latest | `/latest` |
| Author | `/author/{id}` |
| CMS page | `/page/{page_url}` |
| Photos | `/images/news/...`, `/team/...`, `/ads/...`, `/images/logo/...` |
| Admin login | `/login` |
| Admin dashboard | `/admin` |

Old PHP skins, if any remain in the copy, must send the visitor to these same paths. Do not invent a new slug style.

## How speed works

Shared hosting is fast when a ready page is saved and reused.

- LiteSpeed (or the host’s page cache) saves the public HTML after the first visit.
- The next reader gets that saved file. MySQL is not asked again for every click.
- When an editor clicks Publish, or a scheduled story goes live, clear the cache for the home page, that article, that category, and `/latest`.
- Photos keep a long cache. Their file names already change when the picture changes.
- The admin screens are not cached. Editors must always see the fresh form.

Without that cache clear, a new story can sit invisible until the cache expires. The publish button must clear it.

Target for readers: the page should feel as quick as the current site. The current site keeps a page for about 60 seconds inside Node. The PHP site replaces that with the host cache, cleared on publish.

## Date readers see

- Publish now: today’s date, India time, shape `24-09-2026`. No clock time on the public page.
- Schedule for later: the go-live day the editor picked. The story stays hidden until that India time. Then it shows with that day, not the day it was typed.
- Edit a story that is already live: leave its date as it is.
- Stories already on the site keep the dates they have. Do not rewrite old rows.

## Public pages to rebuild in PHP

Copy the look from `web/`. The data rules already live in `web/lib/queries.ts`. PHP must follow those rules, not invent new ones.

1. **Home** (`web/app/(home)/page.tsx`)
   - Each story appears at most once on this page.
   - Order: big photo (pinned main news if set, otherwise newest Breaking) → नारद कहिन → ताज़ा समाचार (mixed categories) → other topic blocks.
   - Shorts row from the YouTube settings already in the admin.
   - This order does not change category pages, article pages, or the admin.
2. **Article** (`/news/{newsurl}`)
   - Title (including coloured words already saved in the title), photo, date, byline, body, related stories.
   - Share and view count must keep working.
3. **Category** (`/category/{cat_url}`) including cities.
4. **Latest** (`/latest`).
5. **Author** (`/author/{id}`).
6. **CMS pages** (`/page/{slug}`) such as about and contact, from the pages table.
7. **Header and footer**
   - Logo, city button, notification bell.
   - No install popup.
   - No red “Install App now” strip on the home page.
   - No small app icon beside शहर चुनें.
   - One install row only, inside the three-line menu: ऐप इंस्टॉल करें. Same menu on phone and desktop.
8. **City chooser** — same list of districts as now.
9. **Sitemap and robots** — same public addresses, so Google does not lose pages.
10. **Ads** — the slots the current site already prints (rail and AdSense), fed from the same admin data.

## Admin (keep, do not redesign)

Editors keep using the PHP admin they have:

- Add news, edit news, schedule, publish now.
- Categories, cities, pages, team/authors, logo, ads.
- Pin one story to the big homepage photo.
- Coloured words in a headline.
- Breaking flag.

Scheduled stories go live from the existing cron idea (`cron_publish_scheduled.php`), adapted to shared hosting. Shared hosting often cannot use a Linux cron the VPS uses. Use the host’s cron screen to call that PHP file every minute, or a reliable equivalent. If that job is missing, a scheduled story never appears.

## Database

Same MySQL tables the site uses now (`news`, `categories`, `news_cat`, `team`, pages, ads, settings). The copy gets its own database. Do not point a shared-hosting experiment at the live production database.

`config.php` in the copy holds that copy’s domain and database name. Production on the VPS stays `thenaradmuni.com` and `paldigitalnews`. Never copy the VPS config into Git, and never copy a localhost config onto a live server.

## Build order in the copied folder

1. Copy the project. Work only in the copy.
2. New public PHP folder that prints the current design. Leave `web/` in the copy as the picture to match. Do not keep running it.
3. Home page first, with the same blocks and the “each story once” rule. Compare it next to the live site.
4. Article, category, latest, author, CMS page.
5. Header, footer, city chooser, one menu install link. No popup.
6. Wire Publish and the schedule job so a new story shows, and the cache clears.
7. Sitemap, robots, ads, view count, share image.
8. Put the copy on a spare shared-hosting domain. Test on the phone and on the desktop.
9. Only after that spare domain matches the live site, decide about moving thenaradmuni.com. Not before.

## Done means

- A visitor on the spare domain sees the same layout as thenaradmuni.com.
- No Node process is running for that copy.
- Publish in the admin. Refresh the home page. The story is there, with today’s date.
- Schedule a story for later. It stays hidden. After that time it appears with the go-live day.
- `/news/...` and `/category/...` match the database values.
- The install card does not pop up. The only install control is the menu row.
- The homepage still feels immediate with the cache on.
