/**
 * HashMyTag Social Wall Widget
 * Version: 1.0.0
 * 
 * Usage:
 * <div id="hashmytag-wall"></div>
 * <script src="https://yourdomain.com/widget.js"></script>
 * <script>
 *   HashMyTag.init({
 *     tenantId: 'your-tenant-id',
 *     apiKey: 'your-api-key',
 *     theme: 'light',
 *     direction: 'vertical',
 *     speed: 'medium',
 *     gamification: true,
 *     fullscreen: true
 *   });
 * </script>
 */

(function() {
    'use strict';

    const HashMyTag = {
        config: {
            apiUrl: window.location.origin,
            container: '#hashmytag-wall',
            tenantId: null,
            apiKey: null,
            theme: 'light',
            direction: 'vertical',
            speed: 'medium',
            gamification: true,
            fullscreen: true,
            autoplay: true,
            postsPerView: 3,
            refreshInterval: 300000, // 5 minutes
        },

        posts: [],
        currentIndex: 0,
        isFullscreen: false,
        isPaused: false,
        scrollInterval: null,
        container: null,

        /**
         * Initialize the widget
         */
        init: function(options) {
            // Merge user options with defaults
            this.config = { ...this.config, ...options };

            // Get container
            this.container = document.querySelector(this.config.container);
            
            if (!this.container) {
                console.error('HashMyTag: Container not found');
                return;
            }

            // Load configuration and posts
            this.loadConfig();
        },

        /**
         * Load widget configuration from API
         */
        loadConfig: async function() {
            try {
                const response = await fetch(`${this.config.apiUrl}/api/widget/config?api_key=${this.config.apiKey}`);
                const data = await response.json();

                if (data.settings) {
                    this.config = { ...this.config, ...data.settings };
                }

                this.loadPosts();
            } catch (error) {
                console.error('HashMyTag: Failed to load config', error);
                this.showError('Failed to load configuration');
            }
        },

        /**
         * Load posts from API
         */
        loadPosts: async function() {
            try {
                const response = await fetch(`${this.config.apiUrl}/api/widget/posts?api_key=${this.config.apiKey}&limit=50`);
                const data = await response.json();

                if (data.posts) {
                    this.posts = data.posts;
                    this.render();
                    
                    if (this.config.autoplay) {
                        this.startAutoScroll();
                    }

                    // Setup refresh interval
                    setInterval(() => this.loadPosts(), this.config.refreshInterval);
                }
            } catch (error) {
                console.error('HashMyTag: Failed to load posts', error);
                this.showError('Failed to load posts');
            }
        },

        /**
         * Render the widget
         */
        render: function() {
            this.container.innerHTML = '';
            this.container.className = `hashmytag-widget theme-${this.config.theme} direction-${this.config.direction}`;

            // Add styles
            this.injectStyles();

            // Create wrapper
            const wrapper = document.createElement('div');
            wrapper.className = 'hashmytag-wrapper';

            // Add header
            if (this.config.fullscreen) {
                const header = this.createHeader();
                wrapper.appendChild(header);
            }

            // Add posts container
            const postsContainer = document.createElement('div');
            postsContainer.className = 'hashmytag-posts';
            postsContainer.id = 'hashmytag-posts-container';

            // Render posts
            this.posts.forEach((post, index) => {
                const postElement = this.createPostElement(post, index);
                postsContainer.appendChild(postElement);
            });

            wrapper.appendChild(postsContainer);

            // Add controls if gamification enabled
            if (this.config.gamification) {
                const controls = this.createControls();
                wrapper.appendChild(controls);
            }

            this.container.appendChild(wrapper);

            // Add event listeners
            this.attachEventListeners();
        },

        /**
         * Create header with fullscreen button
         */
        createHeader: function() {
            const header = document.createElement('div');
            header.className = 'hashmytag-header';
            header.innerHTML = `
                <div class="hashmytag-logo">Social Wall</div>
                <button class="hashmytag-fullscreen-btn" id="fullscreen-toggle">
                    <svg width="20" height="20" viewBox="0 0 20 20">
                        <path fill="currentColor" d="M2 2h6v2H4v4H2V2zm14 0h-6v2h4v4h2V2zM2 18h6v-2H4v-4H2v6zm14 0h-6v-2h4v-4h2v6z"/>
                    </svg>
                </button>
            `;
            return header;
        },

        /**
         * Create a post element
         */
        createPostElement: function(post, index) {
            const article = document.createElement('article');
            article.className = `hashmytag-post platform-${post.platform}`;
            article.dataset.index = index;
            article.dataset.postId = post.id;

            if (post.is_new && this.config.gamification) {
                article.classList.add('is-new');
            }

            if (post.is_highlighted) {
                article.classList.add('is-highlighted');
            }

            // Post header
            const header = `
                <div class="post-header">
                    <div class="post-author">
                        ${post.author.avatar ? `<img src="${post.author.avatar}" alt="${post.author.name}" class="author-avatar">` : '<div class="author-avatar-placeholder"></div>'}
                        <div class="author-info">
                            <div class="author-name">${this.escapeHtml(post.author.name)}</div>
                            ${post.author.username ? `<div class="author-username">@${this.escapeHtml(post.author.username)}</div>` : ''}
                        </div>
                    </div>
                    <div class="post-platform">
                        ${this.getPlatformIcon(post.platform)}
                    </div>
                </div>
            `;

            // Post content
            const content = `
                <div class="post-content">
                    ${post.content ? `<p>${this.linkifyHashtags(this.escapeHtml(post.content))}</p>` : ''}
                    ${post.rating ? `<div class="post-rating">${this.renderStars(post.rating)}</div>` : ''}
                </div>
            `;

            // Post media
            let media = '';
            if (post.media && post.media.length > 0) {
                const firstMedia = post.media[0];
                if (firstMedia.type === 'image') {
                    media = `<div class="post-media"><img src="${firstMedia.url}" alt="Post image" loading="lazy"></div>`;
                } else if (firstMedia.type === 'video') {
                    media = `<div class="post-media"><video src="${firstMedia.url}" ${firstMedia.thumbnail ? `poster="${firstMedia.thumbnail}"` : ''} controls></video></div>`;
                }
            }

            // Post footer
            const footer = `
                <div class="post-footer">
                    <div class="post-stats">
                        ${post.stats.likes > 0 ? `<span class="stat-item">❤️ ${this.formatNumber(post.stats.likes)}</span>` : ''}
                        ${post.stats.comments > 0 ? `<span class="stat-item">💬 ${this.formatNumber(post.stats.comments)}</span>` : ''}
                        ${post.stats.shares > 0 ? `<span class="stat-item">🔁 ${this.formatNumber(post.stats.shares)}</span>` : ''}
                    </div>
                    <div class="post-time">${this.formatTime(post.posted_at)}</div>
                </div>
            `;

            // Gamification badge
            let badge = '';
            if (post.is_new && this.config.gamification) {
                badge = '<div class="gamification-badge">✨ Nouveau</div>';
            }

            article.innerHTML = badge + header + content + media + footer;

            return article;
        },

        /**
         * Create controls
         */
        createControls: function() {
            const controls = document.createElement('div');
            controls.className = 'hashmytag-controls';
            controls.innerHTML = `
                <button class="control-btn" id="play-pause-btn">
                    <svg width="16" height="16" viewBox="0 0 16 16">
                        <path fill="currentColor" d="M3 2v12l10-6z" id="play-icon"/>
                        <path fill="currentColor" d="M3 2h4v12H3zm6 0h4v12H9z" id="pause-icon" style="display:${this.config.autoplay ? 'block' : 'none'}"/>
                    </svg>
                </button>
                <div class="post-counter">
                    <span id="current-post">1</span> / <span id="total-posts">${this.posts.length}</span>
                </div>
            `;
            return controls;
        },

        /**
         * Inject widget styles
         */
        injectStyles: function() {
            if (document.getElementById('hashmytag-styles')) return;

            const style = document.createElement('style');
            style.id = 'hashmytag-styles';
            style.textContent = `
                .hashmytag-widget {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                    position: relative;
                    width: 100%;
                    max-width: 1200px;
                    margin: 0 auto;
                    background: var(--bg-color);
                    color: var(--text-color);
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                }

                .hashmytag-widget.theme-light {
                    --bg-color: #ffffff;
                    --text-color: #1f2937;
                    --header-bg: #f9fafb;
                    --border-color: #e5e7eb;
                    --highlight-color: #3b82f6;
                    --new-badge-bg: #10b981;
                }

                .hashmytag-widget.theme-dark {
                    --bg-color: #1f2937;
                    --text-color: #f9fafb;
                    --header-bg: #111827;
                    --border-color: #374151;
                    --highlight-color: #60a5fa;
                    --new-badge-bg: #34d399;
                }

                .hashmytag-widget.fullscreen {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100vw;
                    height: 100vh;
                    max-width: none;
                    border-radius: 0;
                    z-index: 9999;
                }

                .hashmytag-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 16px 20px;
                    background: var(--header-bg);
                    border-bottom: 1px solid var(--border-color);
                }

                .hashmytag-logo {
                    font-size: 20px;
                    font-weight: 700;
                    color: var(--highlight-color);
                }

                .hashmytag-fullscreen-btn {
                    background: none;
                    border: none;
                    color: var(--text-color);
                    cursor: pointer;
                    padding: 8px;
                    border-radius: 6px;
                    transition: background 0.2s;
                }

                .hashmytag-fullscreen-btn:hover {
                    background: var(--border-color);
                }

                .hashmytag-posts {
                    padding: 20px;
                    display: grid;
                    gap: 20px;
                    max-height: 600px;
                    overflow-y: auto;
                    scroll-behavior: smooth;
                }

                .hashmytag-widget.fullscreen .hashmytag-posts {
                    max-height: calc(100vh - 120px);
                    padding: 40px;
                }

                .hashmytag-widget.direction-vertical .hashmytag-posts {
                    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
                }

                .hashmytag-widget.direction-horizontal .hashmytag-posts {
                    grid-auto-flow: column;
                    grid-auto-columns: 320px;
                    overflow-x: auto;
                }

                .hashmytag-post {
                    background: var(--bg-color);
                    border: 1px solid var(--border-color);
                    border-radius: 12px;
                    padding: 16px;
                    transition: transform 0.3s, box-shadow 0.3s;
                    position: relative;
                    animation: fadeInUp 0.5s ease-out;
                }

                .hashmytag-post:hover {
                    transform: translateY(-4px);
                    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
                }

                .hashmytag-post.is-highlighted {
                    border-color: var(--highlight-color);
                    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
                }

                .gamification-badge {
                    position: absolute;
                    top: 12px;
                    right: 12px;
                    background: var(--new-badge-bg);
                    color: white;
                    padding: 4px 12px;
                    border-radius: 12px;
                    font-size: 12px;
                    font-weight: 600;
                    animation: pulse 2s infinite;
                }

                .post-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 12px;
                }

                .post-author {
                    display: flex;
                    gap: 12px;
                    align-items: center;
                }

                .author-avatar, .author-avatar-placeholder {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    object-fit: cover;
                }

                .author-avatar-placeholder {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }

                .author-name {
                    font-weight: 600;
                    font-size: 14px;
                }

                .author-username {
                    font-size: 12px;
                    color: #6b7280;
                }

                .post-platform {
                    opacity: 0.6;
                }

                .post-content {
                    margin: 12px 0;
                    line-height: 1.5;
                }

                .post-content p {
                    margin: 0;
                    word-wrap: break-word;
                }

                .post-content .hashtag {
                    color: var(--highlight-color);
                    font-weight: 500;
                }

                .post-media {
                    margin: 12px 0;
                    border-radius: 8px;
                    overflow: hidden;
                }

                .post-media img, .post-media video {
                    width: 100%;
                    height: auto;
                    display: block;
                }

                .post-rating {
                    display: flex;
                    gap: 2px;
                    margin-top: 8px;
                    color: #fbbf24;
                }

                .post-footer {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-top: 12px;
                    padding-top: 12px;
                    border-top: 1px solid var(--border-color);
                    font-size: 13px;
                }

                .post-stats {
                    display: flex;
                    gap: 12px;
                }

                .stat-item {
                    display: inline-flex;
                    align-items: center;
                    gap: 4px;
                }

                .post-time {
                    color: #6b7280;
                    font-size: 12px;
                }

                .hashmytag-controls {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    gap: 16px;
                    padding: 16px;
                    background: var(--header-bg);
                    border-top: 1px solid var(--border-color);
                }

                .control-btn {
                    background: var(--highlight-color);
                    border: none;
                    color: white;
                    width: 36px;
                    height: 36px;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: transform 0.2s;
                }

                .control-btn:hover {
                    transform: scale(1.1);
                }

                .post-counter {
                    font-size: 14px;
                    font-weight: 500;
                }

                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @keyframes pulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.05); }
                }

                @media (max-width: 768px) {
                    .hashmytag-posts {
                        grid-template-columns: 1fr !important;
                        padding: 16px;
                    }
                }
            `;
            document.head.appendChild(style);
        },

        /**
         * Attach event listeners
         */
        attachEventListeners: function() {
            // Fullscreen toggle
            const fullscreenBtn = document.getElementById('fullscreen-toggle');
            if (fullscreenBtn) {
                fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());
            }

            // Play/Pause
            const playPauseBtn = document.getElementById('play-pause-btn');
            if (playPauseBtn) {
                playPauseBtn.addEventListener('click', () => this.togglePlayPause());
            }

            // Post hover
            const posts = document.querySelectorAll('.hashmytag-post');
            posts.forEach(post => {
                post.addEventListener('mouseenter', () => this.pauseScroll());
                post.addEventListener('mouseleave', () => this.resumeScroll());
                post.addEventListener('click', (e) => this.trackInteraction(e.currentTarget));
            });

            // Track views
            this.setupIntersectionObserver();
        },

        /**
         * Setup intersection observer for view tracking
         */
        setupIntersectionObserver: function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const postId = entry.target.dataset.postId;
                        this.trackView(postId);
                    }
                });
            }, { threshold: 0.5 });

            document.querySelectorAll('.hashmytag-post').forEach(post => {
                observer.observe(post);
            });
        },

        /**
         * Toggle fullscreen mode
         */
        toggleFullscreen: function() {
            this.isFullscreen = !this.isFullscreen;
            this.container.querySelector('.hashmytag-widget').classList.toggle('fullscreen');
        },

        /**
         * Toggle play/pause
         */
        togglePlayPause: function() {
            this.isPaused = !this.isPaused;
            
            const playIcon = document.getElementById('play-icon');
            const pauseIcon = document.getElementById('pause-icon');
            
            if (this.isPaused) {
                this.stopAutoScroll();
                playIcon.style.display = 'block';
                pauseIcon.style.display = 'none';
            } else {
                this.startAutoScroll();
                playIcon.style.display = 'none';
                pauseIcon.style.display = 'block';
            }
        },

        /**
         * Start auto-scroll
         */
        startAutoScroll: function() {
            const speed = this.config.scroll_speed || 5000;
            
            this.scrollInterval = setInterval(() => {
                if (!this.isPaused) {
                    const container = document.getElementById('hashmytag-posts-container');
                    if (this.config.direction === 'vertical') {
                        container.scrollTop += 1;
                        if (container.scrollTop >= container.scrollHeight - container.clientHeight) {
                            container.scrollTop = 0;
                        }
                    } else {
                        container.scrollLeft += 1;
                        if (container.scrollLeft >= container.scrollWidth - container.clientWidth) {
                            container.scrollLeft = 0;
                        }
                    }
                }
            }, 50);
        },

        /**
         * Stop auto-scroll
         */
        stopAutoScroll: function() {
            if (this.scrollInterval) {
                clearInterval(this.scrollInterval);
                this.scrollInterval = null;
            }
        },

        /**
         * Pause scroll
         */
        pauseScroll: function() {
            this.isPaused = true;
        },

        /**
         * Resume scroll
         */
        resumeScroll: function() {
            if (this.config.autoplay) {
                this.isPaused = false;
            }
        },

        /**
         * Track post view
         */
        trackView: async function(postId) {
            try {
                await fetch(`${this.config.apiUrl}/api/widget/posts/${postId}/view?api_key=${this.config.apiKey}`, {
                    method: 'POST'
                });
            } catch (error) {
                console.error('HashMyTag: Failed to track view', error);
            }
        },

        /**
         * Track post interaction
         */
        trackInteraction: async function(element) {
            const postId = element.dataset.postId;
            
            try {
                await fetch(`${this.config.apiUrl}/api/widget/posts/${postId}/interaction?api_key=${this.config.apiKey}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type: 'click' })
                });
            } catch (error) {
                console.error('HashMyTag: Failed to track interaction', error);
            }
        },

        /**
         * Utility functions
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        linkifyHashtags: function(text) {
            return text.replace(/#(\w+)/g, '<span class="hashtag">#$1</span>');
        },

        formatNumber: function(num) {
            if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
            if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
            return num.toString();
        },

        formatTime: function(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);

            if (diff < 60) return 'À l\'instant';
            if (diff < 3600) return Math.floor(diff / 60) + 'min';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h';
            if (diff < 604800) return Math.floor(diff / 86400) + 'j';
            
            return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
        },

        getPlatformIcon: function(platform) {
            const icons = {
                instagram: '📷',
                facebook: '👍',
                twitter: '🐦',
                google: '⭐'
            };
            return icons[platform] || '📱';
        },

        renderStars: function(rating) {
            const fullStars = Math.floor(rating);
            const hasHalfStar = rating % 1 >= 0.5;
            let stars = '';
            
            for (let i = 0; i < fullStars; i++) {
                stars += '★';
            }
            
            if (hasHalfStar) {
                stars += '½';
            }
            
            return stars;
        },

        showError: function(message) {
            this.container.innerHTML = `
                <div style="padding: 40px; text-align: center; color: #ef4444;">
                    <p style="font-size: 18px; font-weight: 600;">Error</p>
                    <p>${message}</p>
                </div>
            `;
        }
    };

    // Expose to global scope
    window.HashMyTag = HashMyTag;

})();

