> ⚠️ **AI-Generated Code Notice**
> This project was entirely coded by an AI assistant (Claude by Anthropic). It is provided as-is for demonstration purposes. Review the code carefully before using it in production.

# FlipBook3D

A lightweight, zero-dependency JavaScript plugin that renders PDFs (or image arrays) as a realistic 3D flip book in the browser. Built on the Web Audio API and PDF.js — no build tools required.

## Demo

[Live Demo](https://flipbook3d.vercel.app/)

## Features

- Realistic 3D page-turn animation with CSS perspective
- PDF rendering via [PDF.js](https://mozilla.github.io/pdf.js/) — up to ~200 pages
- Image array support (`loadImages`)
- Page flip sound effect (synthesized via Web Audio API)
- Mute button
- Fullscreen mode that scales to fit the screen
- Keyboard navigation (`←` `→` `Home` `End`)
- Touch/swipe support
- First/Prev/Next/Last controls with disabled states
- Responsive — reflows on window resize

## Files

```
dist/
  flipbook3d.js    — Plugin class (FlipBook3D)
  flipbook3d.css   — Plugin styles
index.html         — Live demo with PDF upload
vercel.json        — Vercel deployment config
```

## Quick Start

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

### 3. Initialize and load a PDF

```js
const fb = new FlipBook3D('#my-flipbook', {
    width: Math.min(860, window.innerWidth - 40),
    aspectRatio: 1.42,
    flipDuration: 650,
    scale: 1.8,
});

// Load from a URL
fb.loadPDF('path/to/document.pdf');

// Or from a File input (ArrayBuffer)
input.addEventListener('change', async (e) => {
    const buffer = await e.target.files[0].arrayBuffer();
    await fb.loadPDF(buffer);
});
```

## Options

| Option | Type | Default | Description |
|---|---|---|---|
| `width` | `number` | `min(900, innerWidth - 40)` | Total book width in px (both pages combined) |
| `height` | `number\|null` | `null` | Book height in px. If `null`, derived from `aspectRatio` |
| `aspectRatio` | `number` | `1.414` | Height-to-width ratio per page (A4 = 1.414) |
| `scale` | `number` | `1.5` | Internal canvas render scale — higher = sharper, more memory |
| `flipDuration` | `number` | `700` | Page-turn animation duration in milliseconds |
| `pageBackground` | `string` | `'#fff'` | Page background colour |

## API

```js
// Navigate
fb.nextSpread();          // Go to next two pages
fb.prevSpread();          // Go to previous two pages
fb.goTo(pageIndex);       // Jump to a specific page (0-based)

// Load content
await fb.loadPDF(source); // source: URL string or ArrayBuffer
await fb.loadImages(urls); // Array of image URLs

// Sound
fb.muted = true;          // Mute page-flip sound
fb.muted = false;         // Unmute

// Resize (called automatically on fullscreen change)
fb._resize(totalWidthPx);

// Teardown
fb.destroy();
```

## CSS Custom Properties

Override these in your own stylesheet to theme the plugin:

```css
:root {
    --fb-page-bg: #fff;          /* Page background */
    --fb-spine-color: #c8a96e;   /* Book spine gradient colour */
    --fb-accent: #e8c87d;        /* UI accent colour (buttons, controls) */
    --fb-ui-bg: rgba(10,10,20,0.85); /* Controls bar background */
    --fb-ui-text: #f0e8d8;       /* Controls bar text colour */
}
```

## Browser Support

| Feature | Requirement |
|---|---|
| 3D flip animation | CSS `transform-style: preserve-3d` — all modern browsers |
| PDF rendering | PDF.js 3.x |
| Page flip sound | Web Audio API — all modern browsers (silently skipped if unavailable) |
| Fullscreen | Fullscreen API — all modern browsers |

## Running the Demo

No build step needed. Just open `index.html` in a browser (or serve from a local server to avoid CORS issues with PDF.js):

```bash
# Python
python3 -m http.server 8080

# Node.js (npx)
npx serve .
```

Then open `http://localhost:8080` and upload any PDF.

## License

MIT
