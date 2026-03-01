> ⚠️ **AI-Generated Code Notice**
> This project was entirely coded by an AI assistant (Claude by Anthropic). It is provided as-is for demonstration purposes. Review the code carefully before using it in production.

# FlipBook3D

A lightweight JavaScript plugin that renders PDFs (or image arrays) as a realistic 3D flip book in the browser. Available as a **vanilla JS plugin** and as a **WordPress Elementor widget**.

Built on PDF.js and the Web Audio API — no build tools required.

## Demo

[Live Demo](https://flipbook3d.vercel.app/)

---

## Features

- Realistic 3D page-turn animation with CSS perspective
- PDF rendering via [PDF.js](https://mozilla.github.io/pdf.js/) — up to ~200 pages
- Image gallery support (`loadImages`)
- Page flip sound effect (synthesised via Web Audio API)
- Mute / unmute button
- True fullscreen mode that scales to fill the screen
- Keyboard navigation (`←` `→` `Home` `End`)
- Touch / swipe support
- First / Prev / Next / Last controls with disabled states
- Responsive — reflows on window resize

---

## WordPress Elementor Widget

### Download

**[⬇ Download flipbook3d-elementor.zip](https://github.com/raselupm/flipbook3d/raw/master/dist/flipbook3d-elementor.zip)**

A ready-to-install WordPress plugin that adds a fully-configurable FlipBook3D widget to Elementor. Everything is bundled — no separate asset installation needed.

### Requirements

| Requirement | Minimum version |
|---|---|
| WordPress | 5.9 |
| Elementor | 3.0 |
| PHP | 7.4 |

### Installation

1. Download `flipbook3d-elementor.zip` from the link above.
2. In your WordPress dashboard go to **Plugins → Add New → Upload Plugin**.
3. Choose the zip file and click **Install Now**, then **Activate**.
4. Open any page in Elementor, search for **FlipBook3D** in the widget panel and drag it onto the canvas.

### Plugin File Structure

```
flipbook3d-elementor/
├── flipbook3d-elementor.php   — Main plugin bootstrap
├── widgets/
│   └── flipbook3d-widget.php  — Elementor widget & all controls
└── assets/
    ├── css/flipbook3d.css     — Bundled styles
    └── js/
        ├── flipbook3d.js      — Bundled core plugin
        └── frontend.js        — Elementor frontend handler
```

### Widget Settings Reference

All settings are available in the Elementor panel. No code required.

#### Content Tab — Content Source

| Setting | Type | Description |
|---|---|---|
| Source Type | Select | **PDF — External URL** · **PDF — Media Library** · **Image Gallery** |
| PDF URL | URL | Full URL of your PDF. The server must send CORS headers (`Access-Control-Allow-Origin`). |
| PDF File | Media picker | Select or upload a PDF from your WordPress Media Library. |
| Image Gallery | Gallery picker | Each image becomes one page. Use images with consistent dimensions. |

#### Content Tab — Book Dimensions

| Setting | Type | Default | Description |
|---|---|---|---|
| Book Width | Slider | 900 px | Total width of the open book (both pages combined). Automatically capped to the widget container width. |
| Page Aspect Ratio | Select | A4 Portrait | Presets: A4 Portrait (1.414) · US Letter Portrait (1.294) · A4 Landscape (0.707) · US Letter Landscape (0.773) · Square (1.0) · Magazine (1.5) · Custom |
| Custom Aspect Ratio | Number | 1.414 | Height ÷ width ratio per page. Visible only when **Custom** is selected. |

#### Content Tab — Animation & Rendering

| Setting | Type | Default | Description |
|---|---|---|---|
| Flip Duration | Slider | 700 ms | Duration of the 3D page-turn animation. 200–400 ms feels snappy; 700–1000 ms feels cinematic. |
| Render Scale | Slider | 1.5 | Internal canvas resolution multiplier. Higher = sharper but more GPU/RAM. 1.5–2 is recommended. |
| Page Background Colour | Colour | `#ffffff` | Fill shown behind page content — useful for transparent images or PDFs without a white background. |

#### Content Tab — Controls & Behaviour

| Setting | Type | Default | Description |
|---|---|---|---|
| Start Page | Number | 1 | Which page to open when the book first loads. |
| Page-Flip Sound | Switcher | Enabled | Synthesised page-rustle sound via Web Audio API. Mute it here if unwanted. |
| Navigation Bar | Switcher | Visible | Show or hide the bottom bar with First / Prev / Next / Last buttons. |
| Page Number Overlay | Switcher | Visible | Small page-number label in the corner of each page. |
| Fullscreen Button | Switcher | Visible | Show/hide the fullscreen toggle inside the navigation bar. |
| Click Page to Flip | Switcher | Enabled | Allow clicking on the left/right page edges to turn pages. |

#### Style Tab — Theme Colours

| Setting | CSS Variable | Default | Description |
|---|---|---|---|
| Accent Colour | `--fb-accent` | `#e8c87d` | Button borders, hover highlights, arrow indicators. |
| Spine Colour | `--fb-spine-color` | `#c8a96e` | The vertical book spine strip at the centre of the spread. |
| Page Background (CSS) | `--fb-page-bg` | `#ffffff` | CSS variable counterpart of the Page Background setting. |
| Bar Background | `--fb-ui-bg` | `rgba(10,10,20,0.85)` | Navigation bar background (supports alpha). |
| Bar Text Colour | `--fb-ui-text` | `#f0e8d8` | Page-info text and icon colour inside the navigation bar. |

#### Style Tab — Typography

| Setting | Selector | Default |
|---|---|---|
| Button Font Size | `.flipbook3d-btn`, `.flipbook3d-page-info` | 13 px |
| Page Number Font Size | `.flipbook3d-page-num` | 11 px |

#### Style Tab — Navigation Bar Style

| Setting | Description |
|---|---|
| Bar Border Radius | Rounds the corners of the controls pill. Default 40 px. |
| Bar Padding | Inner spacing of the controls bar. |
| Box Shadow | Elementor box-shadow group control. |

#### Style Tab — Widget Wrapper

| Setting | Description |
|---|---|
| Alignment | Left · Center · Right alignment of the book inside the column. |
| Padding | Outer spacing around the widget. |
| Background Colour | Wrapper background (useful for dark theatre-style presentation). |
| Book Box Shadow | Drop shadow applied to the book stage. |

---

## Vanilla JS — Quick Start

### 1. Include the files

```html
<!-- PDF.js (required for PDF loading) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<!-- FlipBook3D -->
<link rel="stylesheet" href="dist/flipbook3d.css">
<script src="dist/flipbook3d.js"></script>
```

### 2. Add a container

```html
<div id="my-flipbook"></div>
```

### 3. Initialise and load a PDF

```js
const fb = new FlipBook3D('#my-flipbook', {
    width: Math.min(860, window.innerWidth - 40),
    aspectRatio: 1.414,
    flipDuration: 700,
    scale: 1.5,
});

// Load from a URL
fb.loadPDF('path/to/document.pdf');

// Or from a File input (ArrayBuffer)
input.addEventListener('change', async (e) => {
    const buffer = await e.target.files[0].arrayBuffer();
    await fb.loadPDF(buffer);
});
```

---

## Options

| Option | Type | Default | Description |
|---|---|---|---|
| `width` | `number` | `min(900, innerWidth - 40)` | Total book width in px (both pages combined) |
| `height` | `number\|null` | `null` | Book height in px. If `null`, derived from `aspectRatio` |
| `aspectRatio` | `number` | `1.414` | Height-to-width ratio per page (A4 = 1.414) |
| `scale` | `number` | `1.5` | Internal canvas render scale — higher = sharper, more memory |
| `flipDuration` | `number` | `700` | Page-turn animation duration in milliseconds |
| `pageBackground` | `string` | `'#fff'` | Page background colour |

---

## API

```js
// Navigate
fb.nextSpread();           // Go to next two pages
fb.prevSpread();           // Go to previous two pages
fb.goTo(pageIndex);        // Jump to a specific page (0-based)

// Load content
await fb.loadPDF(source);  // source: URL string or ArrayBuffer
await fb.loadImages(urls); // Array of image URLs

// Sound
fb.muted = true;           // Mute page-flip sound
fb.muted = false;          // Unmute

// Resize (called automatically on fullscreen change and window resize)
fb._resize(totalWidthPx);

// Teardown
fb.destroy();
```

---

## CSS Custom Properties

Override these in your own stylesheet to theme the plugin:

```css
:root {
    --fb-page-bg: #fff;              /* Page background */
    --fb-spine-color: #c8a96e;       /* Book spine gradient colour */
    --fb-accent: #e8c87d;            /* UI accent colour (buttons, controls) */
    --fb-ui-bg: rgba(10,10,20,0.85); /* Controls bar background */
    --fb-ui-text: #f0e8d8;           /* Controls bar text colour */
}
```

---

## File Structure

```
dist/
  flipbook3d.js               — Vanilla JS plugin class (FlipBook3D)
  flipbook3d.css              — Vanilla JS plugin styles
  flipbook3d-elementor/       — WordPress Elementor plugin folder
  flipbook3d-elementor.zip    — Ready-to-install WordPress plugin
index.html                    — Live demo with PDF upload
vercel.json                   — Vercel deployment config
```

---

## Browser Support

| Feature | Requirement |
|---|---|
| 3D flip animation | CSS `transform-style: preserve-3d` — all modern browsers |
| PDF rendering | PDF.js 3.x |
| Page flip sound | Web Audio API — all modern browsers (silently skipped if unavailable) |
| Fullscreen | Fullscreen API — all modern browsers |

---

## Running the Demo Locally

No build step needed. Open `index.html` in a browser, or serve from a local server to avoid CORS issues with PDF.js:

```bash
# Python
python3 -m http.server 8080

# Node.js
npx serve .
```

Then open `http://localhost:8080` and upload any PDF.

---

## License

MIT
