(function () {
    'use strict'

    const C = 'main .container'
    const SCRIPT_ID = 'spa-scripts'
    const STYLE_S = 'spa-styles-start'
    const STYLE_E = 'spa-styles-end'

    const SPA = {
        loadedScripts: new Set(),
        _loading: false,

        async init() {
            document.querySelectorAll('script[src]').forEach(s => {
                if (s.src) this.loadedScripts.add(s.src)
            })
            this.box = document.querySelector(C)
            if (!this.box) return
            this.bindLinks()
            this.bindForms()
        },

        bindLinks() {
            document.addEventListener('click', e => {
                const link = e.target.closest('a')
                if (!link) return
                if (link.hasAttribute('data-spa-ignore') || link.hasAttribute('target')) return

                const href = link.getAttribute('href')
                if (!href || href.startsWith('#') || href.startsWith('javascript:') ||
                    href.startsWith('http') || href.startsWith('//') || href.startsWith('tel:') ||
                    href.startsWith('mailto:') || href.includes('/logout')) return

                e.preventDefault()
                this.go(href)
            })
        },

        bindForms() {
            document.addEventListener('submit', e => {
                const form = e.target
                if (form.hasAttribute('data-spa-ignore') || form.action.includes('logout')) return
                e.preventDefault()
                this.sendForm(form)
            })
        },

        async go(url) {
            if (this._loading) return
            this._loading = true
            this.showLoading()

            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                if (!res.ok) throw new Error(String(res.status))
                const html = await res.text()
                const doc = new DOMParser().parseFromString(html, 'text/html')
                this.swap(doc, url)
            } catch (err) {
                console.warn('SPA fallback:', err)
                window.location.href = url
            } finally {
                this._loading = false
                this.hideLoading()
            }
        },

        swap(doc, url) {
            const nc = doc.querySelector(C)
            if (!nc) { window.location.href = url; return }

            this.box.innerHTML = nc.innerHTML
            this.box.classList.remove('spa-fade')
            void this.box.offsetWidth
            this.box.classList.add('spa-fade')

            this.swapStyles(doc)
            this.execScripts(doc)

            const t = doc.querySelector('title')
            if (t) document.title = t.textContent

            this.updateNav(url)
            this.updateBreadcrumb(doc)
            window.scrollTo({ top: 0, behavior: 'smooth' })
            this.updatePageTitle(doc)
        },

        swapStyles(doc) {
            const start = document.getElementById(STYLE_S)
            const end = document.getElementById(STYLE_E)
            const ns = doc.getElementById(STYLE_S)
            const ne = doc.getElementById(STYLE_E)
            if (!start || !end || !ns || !ne) return

            let cur = start.nextElementSibling
            const toRemove = []
            while (cur && cur !== end) {
                toRemove.push(cur)
                cur = cur.nextElementSibling
            }
            toRemove.forEach(el => el.remove())

            let node = ns.nextElementSibling
            const frag = document.createDocumentFragment()
            while (node && node !== ne) {
                frag.appendChild(node.cloneNode(true))
                node = node.nextElementSibling
            }
            end.parentNode.insertBefore(frag, end)
        },

        execScripts(doc) {
            const oldZone = document.getElementById(SCRIPT_ID)
            const newZone = doc.getElementById(SCRIPT_ID)
            if (!newZone) return

            const temp = document.createElement('div')
            temp.id = SCRIPT_ID

            newZone.querySelectorAll('script').forEach(os => {
                const src = os.getAttribute('src')
                const s = document.createElement('script')
                if (src) {
                    if (this.loadedScripts.has(src)) return
                    this.loadedScripts.add(src)
                    s.src = src
                    if (os.integrity) s.integrity = os.integrity
                    if (os.crossOrigin) s.crossOrigin = os.crossOrigin
                } else {
                    s.textContent = os.textContent
                }
                temp.appendChild(s)
            })

            if (oldZone) oldZone.replaceWith(temp)
            else document.body.appendChild(temp)
        },

        async sendForm(form) {
            this.showLoading()
            const fd = new FormData(form)
            const url = form.action
            const method = form.method.toUpperCase()

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    body: method === 'GET' ? null : fd,
                    redirect: 'follow'
                })
                const fu = res.url
                const html = await res.text()

                if (fu !== window.location.href) {
                    await this.go(fu)
                } else {
                    const doc = new DOMParser().parseFromString(html, 'text/html')
                    this.swap(doc, fu)
                }
            } catch (err) {
                console.warn('SPA form fallback:', err)
                form.submit()
            } finally {
                this.hideLoading()
            }
        },

        updateNav(url) {
            document.querySelectorAll('.nav-link.active, .dropdown-item.active').forEach(el => el.classList.remove('active'))

            document.querySelectorAll('.nav-link, .dropdown-item').forEach(link => {
                const href = link.getAttribute('href')
                if (!href) return
                if (url === href || (href !== '/' && url.startsWith(href))) {
                    link.classList.add('active')
                }
            })

            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                const has = menu.querySelector('.dropdown-item.active')
                const tog = menu.previousElementSibling
                if (has) {
                    menu.classList.add('open')
                    if (tog && tog.classList.contains('dropdown-toggle')) tog.classList.add('open')
                }
            })
        },

        updateBreadcrumb(doc) {
            const oldSection = document.querySelector('.header-bar + div, .header-bar + [class*="border-b"]')
            const newSection = doc.querySelector('.header-bar + div, .header-bar + [class*="border-b"]')
            if (oldSection && newSection) {
                oldSection.outerHTML = newSection.outerHTML
            }
        },

        updatePageTitle(doc) {
            const h1 = doc.querySelector('h1.text-lg.font-semibold')
            const curH1 = document.querySelector('h1.text-lg.font-semibold')
            if (h1 && curH1) curH1.textContent = h1.textContent
        },

        showLoading() {
            if (!document.getElementById('spa-loading')) {
                const d = document.createElement('div')
                d.id = 'spa-loading'
                d.innerHTML = '<div class="spa-loader-bar"></div>'
                document.body.appendChild(d)
            }
        },

        hideLoading() {
            const el = document.getElementById('spa-loading')
            if (el) el.remove()
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => SPA.init())
    } else {
        SPA.init()
    }
})()
