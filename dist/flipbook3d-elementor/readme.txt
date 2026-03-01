=== FlipBook3D — Elementor Widget ===
Contributors: raselahmed7
Tags: flipbook, pdf viewer, elementor, page flip, 3d book
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT

A realistic 3D PDF flip-book Elementor widget. Load any PDF or image gallery and let visitors flip through pages with smooth 3D animations.

== Description ==

**FlipBook3D** adds a fully-configurable, realistic 3D page-turn widget to Elementor. Drop it onto any page, point it at a PDF or image gallery, and your visitors get a beautiful book-reading experience — no coding required.

= Key Features =

* **3D page-turn animation** — smooth CSS perspective-based flip with configurable duration
* **PDF support** — renders any PDF directly in the browser via PDF.js (no server-side processing)
* **Image gallery mode** — turn any set of images into a flipbook, one image per page
* **Media Library integration** — upload and select PDFs straight from the WordPress Media Library
* **External PDF URL** — link to any publicly accessible PDF (CORS-enabled)
* **Page-flip sound** — synthesised page-rustle effect via the Web Audio API (can be muted)
* **Fullscreen mode** — scales to fill the entire screen with one click
* **Keyboard navigation** — left/right arrow keys, Home and End
* **Touch and swipe** — works on phones and tablets
* **Navigation bar** — First / Prev / Next / Last buttons with disabled-state indicators
* **Page number overlay** — small label shown in the corner of each page
* **Responsive** — automatically reflows on window resize and fits inside any Elementor column
* **Elementor Improved Asset Loading** — scripts and styles are only loaded on pages that use the widget

= Elementor Controls =

All settings are accessible from the Elementor panel — no shortcodes or PHP required.

**Content Tab**

* *Source Type* — PDF (External URL), PDF (Media Library), or Image Gallery
* *Book Width* — total width of the open spread in pixels (auto-capped to the column)
* *Page Aspect Ratio* — presets for A4, US Letter, Square, Magazine, or a custom ratio
* *Flip Duration* — animation speed in milliseconds
* *Render Scale* — internal canvas resolution (1.5–2 recommended for sharp text)
* *Page Background Colour* — fill behind PDF or transparent-image pages
* *Start Page* — which page to open on first load
* *Page-Flip Sound* — enable or mute the synthesised sound
* *Navigation Bar* — show or hide the bottom controls
* *Page Number Overlay* — show or hide the per-page number label
* *Fullscreen Button* — show or hide the fullscreen toggle
* *Click Page to Flip* — allow clicking the left/right page edges to turn pages

**Style Tab**

* *Accent Colour* — buttons, hover highlights, arrow indicators
* *Spine Colour* — the vertical book-spine strip at the centre of the spread
* *Navigation Bar Background and Text Colour*
* *Button and Page Number Font Sizes*
* *Navigation Bar Border Radius, Padding, and Box Shadow*
* *Widget Wrapper Alignment, Padding, Background, and Book Box Shadow*

== Installation ==

= From your WordPress dashboard =

1. Go to **Plugins → Add New → Upload Plugin**.
2. Click **Choose File**, select `flipbook3d-elementor.zip`, and click **Install Now**.
3. Click **Activate Plugin**.
4. Open any page in Elementor, search for **FlipBook3D** in the widget panel, and drag it onto the canvas.

= From WordPress.org =

1. Search for **FlipBook3D Elementor** in **Plugins → Add New**.
2. Click **Install Now**, then **Activate**.
3. Open any page in Elementor and drag the **FlipBook3D** widget onto the canvas.

= Requirements =

* WordPress 5.9 or later
* Elementor 3.0 or later (free version is sufficient)
* PHP 7.4 or later
* A modern browser (Chrome, Firefox, Safari, Edge)

= Using a PDF from an external URL =

The PDF server must send a permissive `Access-Control-Allow-Origin` CORS header. PDFs hosted in your own WordPress Media Library work without any CORS configuration.

== Frequently Asked Questions ==

= Does this require Elementor Pro? =

No. The widget works with the free version of Elementor.

= Which browsers are supported? =

Any modern browser that supports CSS `transform-style: preserve-3d` and the Web Audio API — Chrome, Firefox, Safari, and Edge all work. The page-flip sound is silently skipped if the Web Audio API is unavailable.

= My external PDF does not load. What should I check? =

The PDF server must include a CORS header such as `Access-Control-Allow-Origin: *`. If the server does not allow cross-origin requests, upload the PDF to your WordPress Media Library instead and use the **PDF — Media Library** source type.

= Can I use images instead of a PDF? =

Yes. Set *Source Type* to **Image Gallery** and select your images from the Elementor gallery picker. Each image becomes one page. Use images with consistent dimensions for the best result.

= How do I control the sharpness of the pages? =

Increase the **Render Scale** control (Style tab). A value of 1.5 to 2 gives sharp text on most screens. Values above 2 may increase GPU and memory usage noticeably.

= Can I start the book on a specific page? =

Yes. Set the **Start Page** control (Content tab → Controls & Behaviour) to the desired page number.

= Is the page-flip sound required? =

No. Toggle **Page-Flip Sound** to *Muted* in the Controls & Behaviour section to disable it.

= Does the widget slow down pages where it is not used? =

No. The widget uses Elementor's Improved Asset Loading feature, so its scripts and styles are only enqueued on pages that actually contain the widget.

== Screenshots ==

1. The FlipBook3D widget rendering a PDF with the navigation bar visible.
2. Elementor panel showing the Content Source controls.
3. Elementor panel showing the Style controls.
4. The widget in fullscreen mode.
5. The editor placeholder shown inside the Elementor canvas before a source is selected.

== Changelog ==

= 1.0.0 =
* Initial release.
* PDF rendering via PDF.js (external URL and Media Library).
* Image gallery mode.
* 3D CSS page-turn animation with configurable duration.
* Synthesised page-flip sound via Web Audio API.
* Fullscreen mode.
* Keyboard and touch/swipe navigation.
* Full set of Elementor Content and Style controls.
* Elementor Improved Asset Loading support.

== Upgrade Notice ==

= 1.0.0 =
Initial release — no upgrade required.
