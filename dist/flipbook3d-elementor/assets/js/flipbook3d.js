/* ============================================
   FlipBook3D — JavaScript Plugin
   https://github.com/raselupm/flipbook3d

   Usage:
     const fb = new FlipBook3D('#container', options);
     fb.loadPDF(url_or_arrayBuffer);
     fb.loadImages([url1, url2, ...]);
============================================ */

class FlipBook3D {
    constructor(selector, options = {}) {
        this.container = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (!this.container) throw new Error('FlipBook3D: container not found');

        this.opts = Object.assign({
            width: Math.min(900, window.innerWidth - 40),
            height: null, // auto from aspect
            aspectRatio: 1.414, // A4 = 1.414 (h/w per page)
            scale: 1.5,
            flipDuration: 700, // ms
            pageBackground: '#fff',
            shadows: true,
        }, options);

        // Compute dimensions
        const pageW = this.opts.width / 2;
        const pageH = this.opts.height || Math.round(pageW * this.opts.aspectRatio);
        this.pageW = pageW;
        this.pageH = pageH;
        this.totalW = pageW * 2;

        this._origWidth = this.opts.width;
        this._audioCtx = null;
        this.muted = false;
        this.pages = [];
        this.currentSpread = 0;
        this.totalPages = 0;
        this.isFlipping = false;
        this._fsChanging = false;
        this.pdfDoc = null;
        this.renderQueue = [];
        this.renderedPages = new Map();

        this._build();
        this._bindEvents();
    }

    /* ------- BUILD DOM ------- */
    _build() {
        const w = this.totalW, h = this.pageH;

        this.container.innerHTML = '';
        const wrapper = document.createElement('div');
        wrapper.className = 'flipbook3d-wrapper';

        // Stage
        this.stage = document.createElement('div');
        this.stage.className = 'flipbook3d-stage';
        this.stage.style.cssText = `width:${w}px;height:${h}px;`;

        // Book
        this.book = document.createElement('div');
        this.book.className = 'flipbook3d-book';
        this.book.style.cssText = `width:${w}px;height:${h}px;`;

        // Spread
        this.spread = document.createElement('div');
        this.spread.className = 'flipbook3d-spread';

        // Left page
        this.pageLeft = document.createElement('div');
        this.pageLeft.className = 'flipbook3d-page-left';
        this.pageLeft.style.height = h + 'px';
        const curlL = document.createElement('div');
        curlL.className = 'page-curl-shadow';
        this.pageLeft.appendChild(curlL);
        this.canvasLeft = document.createElement('canvas');
        this.canvasLeft.width = Math.round(this.pageW * this.opts.scale);
        this.canvasLeft.height = Math.round(this.pageH * this.opts.scale);
        this.pageLeft.appendChild(this.canvasLeft);
        this.pageNumLeft = document.createElement('div');
        this.pageNumLeft.className = 'flipbook3d-page-num';
        this.pageLeft.appendChild(this.pageNumLeft);

        // Right page
        this.pageRight = document.createElement('div');
        this.pageRight.className = 'flipbook3d-page-right';
        this.pageRight.style.height = h + 'px';
        const curlR = document.createElement('div');
        curlR.className = 'page-curl-shadow';
        this.pageRight.appendChild(curlR);
        this.canvasRight = document.createElement('canvas');
        this.canvasRight.width = Math.round(this.pageW * this.opts.scale);
        this.canvasRight.height = Math.round(this.pageH * this.opts.scale);
        this.pageRight.appendChild(this.canvasRight);
        this.pageNumRight = document.createElement('div');
        this.pageNumRight.className = 'flipbook3d-page-num';
        this.pageRight.appendChild(this.pageNumRight);

        // Spine
        this.spine = document.createElement('div');
        this.spine.className = 'flipbook3d-spine';

        // Flipper element (animated)
        this.flipper = document.createElement('div');
        this.flipper.className = 'flipbook3d-flipper';
        this.flipper.style.cssText = `width:${this.pageW}px;height:${h}px;display:none;`;
        this.flipFront = document.createElement('div');
        this.flipFront.className = 'flipbook3d-flipper-front';
        this.canvasFlipFront = document.createElement('canvas');
        this.canvasFlipFront.width = Math.round(this.pageW * this.opts.scale);
        this.canvasFlipFront.height = Math.round(this.pageH * this.opts.scale);
        this.flipFront.appendChild(this.canvasFlipFront);
        this.flipBack = document.createElement('div');
        this.flipBack.className = 'flipbook3d-flipper-back';
        this.canvasFlipBack = document.createElement('canvas');
        this.canvasFlipBack.width = Math.round(this.pageW * this.opts.scale);
        this.canvasFlipBack.height = Math.round(this.pageH * this.opts.scale);
        this.flipBack.appendChild(this.canvasFlipBack);
        this.flipper.appendChild(this.flipFront);
        this.flipper.appendChild(this.flipBack);

        // Click zones
        this.clickLeft = document.createElement('div');
        this.clickLeft.className = 'flipbook3d-click-left';
        this.clickRight = document.createElement('div');
        this.clickRight.className = 'flipbook3d-click-right';

        // Loading
        this.loadingEl = document.createElement('div');
        this.loadingEl.className = 'flipbook3d-loading';
        this.loadingEl.innerHTML = `
      <div class="flipbook3d-loading-spinner"></div>
      <div class="flipbook3d-loading-text">Loading…</div>
    `;

        // Assemble
        this.spread.appendChild(this.pageLeft);
        this.spread.appendChild(this.pageRight);
        this.spread.appendChild(this.spine);
        this.spread.appendChild(this.flipper);
        this.spread.appendChild(this.clickLeft);
        this.spread.appendChild(this.clickRight);
        this.book.appendChild(this.spread);
        this.book.appendChild(this.loadingEl);

        // Controls
        this.controls = document.createElement('div');
        this.controls.className = 'flipbook3d-controls';
        this.controls.innerHTML = `
      <span class="flipbook3d-btn" id="fb-first" title="First page">⏮ First</span>
      <span class="flipbook3d-btn" id="fb-prev" title="Previous page">← Prev</span>
      <span class="flipbook3d-page-info" id="fb-page-info">— / —</span>
      <span class="flipbook3d-btn" id="fb-next" title="Next page">Next →</span>
      <span class="flipbook3d-btn" id="fb-last" title="Last page">Last ⏭</span>
      <span class="flipbook3d-fullscreen-btn" id="fb-fs" title="Fullscreen">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
        </svg>
      </span>
    `;

        this.stage.appendChild(this.book);
        wrapper.appendChild(this.stage);
        wrapper.appendChild(this.controls);
        this.container.appendChild(wrapper);
        this.wrapper = wrapper;

        this._showLoading(false);
    }

    /* ------- EVENTS ------- */
    _bindEvents() {
        const qs = (id) => this.controls.querySelector('#' + id) || this.wrapper.querySelector('#' + id);

        this.btnFirst = qs('fb-first');
        this.btnPrev  = qs('fb-prev');
        this.btnNext  = qs('fb-next');
        this.btnLast  = qs('fb-last');
        this.btnFs    = qs('fb-fs');
        this.pageInfo = qs('fb-page-info');

        this.clickLeft.addEventListener('click', () => this.prevSpread());
        this.clickRight.addEventListener('click', () => this.nextSpread());

        this.btnFirst.addEventListener('click', () => this.goTo(0));
        this.btnPrev.addEventListener('click',  () => this.prevSpread());
        this.btnNext.addEventListener('click',  () => this.nextSpread());
        this.btnLast.addEventListener('click',  () => this.goTo(this.totalPages - 1));
        this.btnFs.addEventListener('click',    () => this._toggleFullscreen());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') this.prevSpread();
            if (e.key === 'ArrowRight') this.nextSpread();
            if (e.key === 'Home') this.goTo(0);
            if (e.key === 'End') this.goTo(this.totalPages - 1);
        });

        // Touch swipe
        let touchX = 0;
        this.stage.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; }, { passive: true });
        this.stage.addEventListener('touchend', e => {
            const dx = e.changedTouches[0].clientX - touchX;
            if (Math.abs(dx) > 50) dx < 0 ? this.nextSpread() : this.prevSpread();
        }, { passive: true });

        // Resize book when entering/exiting fullscreen
        const onFsChange = () => {
            this._fsChanging = true;
            const isFs = !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement);
            if (isFs) {
                // window.screen.width/height are the physical screen dimensions —
                // always available immediately, no timing dependency. This is the
                // only reliable approach on Safari which does not fire a window
                // resize event when an element enters fullscreen.
                const controlsH = this.controls.offsetHeight + 40;
                const availH = window.screen.height - controlsH;
                const availW = window.screen.width;
                const fitByH = Math.round(availH * 2 / this.opts.aspectRatio);
                this._resize(Math.min(availW, fitByH));
            } else {
                this._resize(this._origWidth);
            }
            setTimeout(() => { this._fsChanging = false; }, 300);
        };
        document.addEventListener('fullscreenchange', onFsChange);
        document.addEventListener('webkitfullscreenchange', onFsChange);
        document.addEventListener('mozfullscreenchange', onFsChange);
    }

    /* ------- LOADING ------- */
    _showLoading(show, text = 'Loading…') {
        this.loadingEl.style.display = show ? 'flex' : 'none';
        const t = this.loadingEl.querySelector('.flipbook3d-loading-text');
        if (t) t.textContent = text;
    }

    /* ------- PDF LOADING ------- */
    async loadPDF(source) {
        this._showLoading(true, 'Loading PDF…');
        try {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                ( window.flipbook3dData && window.flipbook3dData.workerSrc )
                    ? window.flipbook3dData.workerSrc
                    : '';

            const loadingTask = typeof source === 'string'
                ? pdfjsLib.getDocument(source)
                : pdfjsLib.getDocument({ data: source });

            this.pdfDoc = await loadingTask.promise;
            this.totalPages = this.pdfDoc.numPages;
            this.renderedPages.clear();
            this.currentSpread = 0;

            this._showLoading(true, `Rendering page 1 of ${this.totalPages}…`);

            // Render both pages of the first spread before showing anything
            await this._renderPDFPage(0);
            if (this.totalPages > 1) await this._renderPDFPage(1);
            this._showLoading(false);
            this._updateSpread();

            // Render remaining pages in background
            this._renderPageRange(2, this.totalPages - 1);
        } catch (err) {
            console.error('FlipBook3D PDF error:', err);
            this._showLoading(true, 'Error loading PDF');
        }
    }

    async _renderPDFPage(pageIndex) {
        if (this.renderedPages.has(pageIndex)) return;
        const page = await this.pdfDoc.getPage(pageIndex + 1);
        const viewport = page.getViewport({ scale: this.opts.scale });
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(this.pageW * this.opts.scale);
        canvas.height = Math.round(this.pageH * this.opts.scale);
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        const scaleX = canvas.width / viewport.width;
        const scaleY = canvas.height / viewport.height;
        const sc = Math.min(scaleX, scaleY);
        const offX = (canvas.width - viewport.width * sc) / 2;
        const offY = (canvas.height - viewport.height * sc) / 2;
        const scaledViewport = page.getViewport({ scale: this.opts.scale * sc });
        await page.render({
            canvasContext: ctx,
            viewport: scaledViewport,
            transform: [1, 0, 0, 1, offX, offY]
        }).promise;
        this.renderedPages.set(pageIndex, canvas);
    }

    async _renderPageRange(from, to) {
        for (let i = from; i <= to; i++) {
            await this._renderPDFPage(i);
        }
    }

    /* ------- IMAGE LOADING ------- */
    async loadImages(urls) {
        this._showLoading(true, 'Loading images…');
        this.pdfDoc = null;
        this.renderedPages.clear();
        this.totalPages = urls.length;
        this.currentSpread = 0;

        const loadImg = (url, idx) => new Promise(resolve => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(this.pageW * this.opts.scale);
                canvas.height = Math.round(this.pageH * this.opts.scale);
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#fff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                const scale = Math.min(canvas.width / img.width, canvas.height / img.height);
                const ox = (canvas.width - img.width * scale) / 2;
                const oy = (canvas.height - img.height * scale) / 2;
                ctx.drawImage(img, ox, oy, img.width * scale, img.height * scale);
                this.renderedPages.set(idx, canvas);
                resolve();
            };
            img.onerror = resolve;
            img.src = url;
        });

        await Promise.all([0, 1].filter(i => i < urls.length).map(i => loadImg(urls[i], i)));
        this._showLoading(false);
        this._updateSpread();
        for (let i = 2; i < urls.length; i++) await loadImg(urls[i], i);
    }

    /* ------- DRAW PAGE ------- */
    _drawToCanvas(canvas, pageIndex) {
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        if (pageIndex < 0 || pageIndex >= this.totalPages) {
            ctx.fillStyle = '#f8f5f0';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            return;
        }

        const src = this.renderedPages.get(pageIndex);
        if (src) {
            ctx.drawImage(src, 0, 0, canvas.width, canvas.height);
        } else {
            ctx.fillStyle = '#f5f0e8';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#c8b080';
            ctx.font = `${Math.round(canvas.height * 0.04)}px Georgia`;
            ctx.textAlign = 'center';
            ctx.fillText(`Page ${pageIndex + 1}`, canvas.width / 2, canvas.height / 2);
        }
    }

    /* ------- UPDATE SPREAD ------- */
    _updateSpread() {
        const leftIdx = this.currentSpread;
        const rightIdx = this.currentSpread + 1;
        this._drawToCanvas(this.canvasLeft, leftIdx);
        this._drawToCanvas(this.canvasRight, rightIdx);
        this._updatePageNums(leftIdx);
        this._updateControls();
        this._preloadAdjacent();
    }

    _preloadAdjacent() {
        if (!this.pdfDoc) return;
        const toLoad = [
            this.currentSpread + 2,
            this.currentSpread + 3,
            this.currentSpread - 2,
            this.currentSpread - 1,
        ];
        for (const idx of toLoad) {
            if (idx >= 0 && idx < this.totalPages && !this.renderedPages.has(idx)) {
                this._renderPDFPage(idx);
            }
        }
    }

    /* ------- FLIP ANIMATION ------- */
    async _flip(direction) {
        if (this.isFlipping) return;

        const nextSpread = direction === 'next'
            ? this.currentSpread + 2
            : this.currentSpread - 2;

        if (nextSpread < 0 || nextSpread >= this.totalPages) return;

        await this._ensurePageReady(nextSpread);
        await this._ensurePageReady(nextSpread + 1);

        this.isFlipping = true;
        this._playPageFlip();
        const dur = this.opts.flipDuration;

        this.flipper.style.transition = 'none';

        if (direction === 'next') {
            this.flipper.className = 'flipbook3d-flipper from-right';
            this.flipper.style.left = this.pageW + 'px';
            this.flipper.style.right = '';
            this.flipper.style.transformOrigin = 'left center';
            this.flipper.style.zIndex = '8';

            this._drawToCanvas(this.canvasFlipFront, this.currentSpread + 1);
            this._drawToCanvas(this.canvasFlipBack, nextSpread);
            this._drawToCanvas(this.canvasRight, nextSpread + 1);

            this.flipper.style.transform = 'rotateY(0deg)';
            this.flipper.style.display = 'block';
            this.flipFront.style.boxShadow = '4px 0 30px rgba(0,0,0,0.25)';

            await this._nextFrame();
            this.flipper.style.transition = `transform ${dur}ms cubic-bezier(0.645,0.045,0.355,1.000)`;
            this.flipper.style.transform = 'rotateY(-180deg)';

            await this._delay(dur);

            this.currentSpread = nextSpread;
            this._drawToCanvas(this.canvasLeft, nextSpread);
            this._updateControls();
            this._updatePageNums(nextSpread);
            this.flipper.style.display = 'none';

        } else {
            this.flipper.className = 'flipbook3d-flipper from-left';
            this.flipper.style.left = '0px';
            this.flipper.style.right = '';
            this.flipper.style.transformOrigin = 'right center';
            this.flipper.style.zIndex = '8';

            this._drawToCanvas(this.canvasFlipFront, this.currentSpread);
            this._drawToCanvas(this.canvasFlipBack, nextSpread + 1);
            this._drawToCanvas(this.canvasLeft, nextSpread);

            this.flipper.style.transform = 'rotateY(0deg)';
            this.flipper.style.display = 'block';

            await this._nextFrame();
            this.flipper.style.transition = `transform ${dur}ms cubic-bezier(0.645,0.045,0.355,1.000)`;
            this.flipper.style.transform = 'rotateY(180deg)';

            await this._delay(dur);

            this.currentSpread = nextSpread;
            this._drawToCanvas(this.canvasRight, nextSpread + 1);
            this._updateControls();
            this._updatePageNums(nextSpread);
            this.flipper.style.display = 'none';
        }

        this.isFlipping = false;
    }

    _updatePageNums(leftIdx) {
        const rightIdx = leftIdx + 1;
        this.pageNumLeft.textContent = (leftIdx >= 0 && leftIdx < this.totalPages) ? leftIdx + 1 : '';
        this.pageNumRight.textContent = (rightIdx >= 0 && rightIdx < this.totalPages) ? rightIdx + 1 : '';
    }

    _updateControls() {
        const leftIdx = this.currentSpread;
        const rightIdx = this.currentSpread + 1;
        if (this.pageInfo) {
            const display = Math.min(rightIdx, this.totalPages - 1) + 1;
            this.pageInfo.textContent = `${leftIdx + 1}\u2013${display} / ${this.totalPages}`;
        }
        const lastSpread = Math.max(0, this.totalPages % 2 === 0 ? this.totalPages - 2 : this.totalPages - 1);
        if (this.btnFirst) this.btnFirst.classList.toggle('disabled', this.currentSpread === 0);
        if (this.btnPrev)  this.btnPrev.classList.toggle('disabled',  this.currentSpread === 0);
        if (this.btnNext)  this.btnNext.classList.toggle('disabled',  this.currentSpread >= lastSpread);
        if (this.btnLast)  this.btnLast.classList.toggle('disabled',  this.currentSpread >= lastSpread);
        this.clickLeft.style.cursor  = this.currentSpread === 0 ? 'default' : 'pointer';
        this.clickRight.style.cursor = this.currentSpread >= lastSpread ? 'default' : 'pointer';
    }

    async _ensurePageReady(pageIndex) {
        if (pageIndex < 0 || pageIndex >= this.totalPages) return;
        if (this.renderedPages.has(pageIndex)) return;
        if (this.pdfDoc) await this._renderPDFPage(pageIndex);
    }

    _nextFrame() {
        return new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));
    }
    _delay(ms) {
        return new Promise(r => setTimeout(r, ms));
    }

    /* ------- PUBLIC API ------- */
    nextSpread() { this._flip('next'); }
    prevSpread() { this._flip('prev'); }

    async goTo(pageIndex) {
        if (this.isFlipping) return;
        const targetSpread = pageIndex % 2 === 0 ? pageIndex : pageIndex - 1;
        const clampedSpread = Math.max(0, Math.min(targetSpread, this.totalPages - 1));
        if (clampedSpread === this.currentSpread) return;

        const direction = clampedSpread > this.currentSpread ? 'next' : 'prev';
        const steps = Math.abs(clampedSpread - this.currentSpread) / 2;

        if (steps > 3) {
            this.currentSpread = clampedSpread;
            this._updateSpread();
        } else {
            for (let i = 0; i < steps; i++) {
                await this._flip(direction);
            }
        }
    }

    _toggleFullscreen() {
        const isFs = !!(
            document.fullscreenElement ||
            document.webkitFullscreenElement ||
            document.mozFullScreenElement
        );
        if (!isFs) {
            const el = this.wrapper;
            const req = (
                el.requestFullscreen ||
                el.webkitRequestFullscreen ||
                el.mozRequestFullScreen ||
                el.msRequestFullscreen
            );
            if (req) {
                req.call(el).catch(() => {
                    document.documentElement.requestFullscreen && document.documentElement.requestFullscreen();
                });
            }
        } else {
            (
                document.exitFullscreen ||
                document.webkitExitFullscreen ||
                document.mozCancelFullScreen ||
                document.msExitFullscreen
            ).call(document);
        }
    }

    _playPageFlip() {
        if (this.muted) return;
        try {
            if (!this._audioCtx) this._audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const ctx = this._audioCtx;
            const now = ctx.currentTime;
            const dur = 0.18;

            const bufLen = Math.round(ctx.sampleRate * dur);
            const buf = ctx.createBuffer(1, bufLen, ctx.sampleRate);
            const data = buf.getChannelData(0);
            for (let i = 0; i < bufLen; i++) data[i] = (Math.random() * 2 - 1);

            const src = ctx.createBufferSource();
            src.buffer = buf;

            const bp = ctx.createBiquadFilter();
            bp.type = 'bandpass';
            bp.frequency.value = 1800;
            bp.Q.value = 0.8;

            const gain = ctx.createGain();
            gain.gain.setValueAtTime(0, now);
            gain.gain.linearRampToValueAtTime(0.28, now + 0.012);
            gain.gain.exponentialRampToValueAtTime(0.001, now + dur);

            src.connect(bp);
            bp.connect(gain);
            gain.connect(ctx.destination);
            src.start(now);
            src.stop(now + dur);
        } catch (_) { /* AudioContext unavailable — silently skip */ }
    }

    _resize(totalW) {
        const pageW = totalW / 2;
        const pageH = Math.round(pageW * this.opts.aspectRatio);
        this.pageW = pageW;
        this.pageH = pageH;
        this.totalW = totalW;
        this.stage.style.cssText = `width:${totalW}px;height:${pageH}px;`;
        this.book.style.cssText = `width:${totalW}px;height:${pageH}px;`;
        this.pageLeft.style.height = pageH + 'px';
        this.pageRight.style.height = pageH + 'px';
        this.flipper.style.cssText = `width:${pageW}px;height:${pageH}px;display:none;`;
        this._updateSpread();
    }

    destroy() {
        this.container.innerHTML = '';
    }
}
