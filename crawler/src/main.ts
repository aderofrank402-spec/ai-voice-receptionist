// FIX: Disable Crawlee memory monitoring on Windows/Node 22 (ps-tree bug)
// We rely on the Configuration class for a robust programmatic fix.

import { PlaywrightCrawler, Dataset, Configuration } from 'crawlee';
import { chromium } from 'playwright';

// Parse CLI args
const args = process.argv.slice(2);
const urlArg = args.find(arg => arg.startsWith('--url='));
const startUrl = urlArg ? urlArg.split('=')[1] : null;

if (!startUrl) {
    console.error(JSON.stringify({ error: 'Missing --url argument' }));
    process.exit(1);
}

const domain = new URL(startUrl).hostname;

// Robust Configuration to bypass 'ps-tree' bug
const config = new Configuration({
    purgeOnStart: true,              // Clean start each run
    memoryMbytes: 2048,
    persistStorage: false,           // Cleaner runs
});

const crawler = new PlaywrightCrawler({
    minConcurrency: 1,               // Start conservative
    maxConcurrency: 15,              // Cap at 15 for 2GB container safety
    maxRequestsPerCrawl: 20,         // Quality over Quantity
    requestHandlerTimeoutSecs: 30,   // Fail fast on slow pages so rest of crawl can finish
    navigationTimeoutSecs: 30,       // Match handler
    maxRequestRetries: 2,            // Retry once more on net::ERR_SOCKET_NOT_CONNECTED etc.
    useSessionPool: false,           // ⚡ FAST START: No session management overhead

    // 🚀 PERFORMANCE: Docker-optimized browser launch
    launchContext: {
        launchOptions: {
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu', '--disable-dev-shm-usage'],
        },
    },

    // ✅ BEST PRACTICE: Resource blocking in preNavigationHooks
    preNavigationHooks: [
        async ({ page, request, log }) => {
            log.debug(`Setting up resource blocking for ${request.url}`);

            // Clean up previous routes to avoid stacking memory leaks
            await page.unroute('**/*').catch(() => { });

            await page.route('**/*', (route) => {
                const type = route.request().resourceType();
                const url = route.request().url();

                // 🛑 Block heavy assets and known trackers
                const blockedTypes = ['image', 'media', 'font'];
                const blockedDomains = ['google-analytics.com', 'googletagmanager.com', 'facebook.net', 'doubleclick.net'];

                if (blockedTypes.includes(type) || blockedDomains.some(d => url.includes(d))) {
                    route.abort();
                } else {
                    route.continue();
                }
            });
        }
    ],

    requestHandler: async ({ page, request, enqueueLinks, log }) => {
        const title = await page.title();
        log.info(`Processing ${request.url} - ${title}`);

        // 🔍 GOOGLE-STYLE DISCOVERY: Extract and prioritize navigation links on homepage
        const currentUrl = request.url.replace(/\/$/, '');
        const targetUrl = startUrl.replace(/\/$/, '');

        if (currentUrl === targetUrl) {
            log.info(`🔍 Discovering navigation structure from homepage...`);
            
            // Extract links from navigation, header, and footer
            const discoveredLinks = await page.evaluate(() => {
                const links: Array<{ url: string; text: string; source: string; priority: number; label?: string }> = [];
                
                // Priority 1: Main navigation
                document.querySelectorAll('nav a, header a, [role="navigation"] a').forEach(a => {
                    const anchor = a as HTMLAnchorElement;
                    if (anchor.href && anchor.href.startsWith(window.location.origin)) {
                        const text = a.textContent?.trim().toLowerCase() || '';
                        links.push({
                            url: anchor.href,
                            text: text,
                            source: 'navigation',
                            priority: 10
                        });
                    }
                });
                
                // Priority 2: Main content area links (but lower priority)
                document.querySelectorAll('main a, [role="main"] a, .content a').forEach(a => {
                    const anchor = a as HTMLAnchorElement;
                    if (anchor.href && anchor.href.startsWith(window.location.origin)) {
                        const text = a.textContent?.trim().toLowerCase() || '';
                        // Only add if not already in nav links
                        if (!links.some(link => link.url === anchor.href)) {
                            links.push({
                                url: anchor.href,
                                text: text,
                                source: 'content',
                                priority: 5
                            });
                        }
                    }
                });
                
                return links;
            });

            log.info(`📋 Discovered ${discoveredLinks.length} links from homepage`);
            
            // 🛑 BLOCK PATTERNS - Apply BEFORE classifying
            const blockPatterns = [
                /\/blogs?([/?#.]|$)/, /\/insights?([/?#.]|$)/, /\/news([/?#.]|$)/,
                /\/articles?([/?#.]|$)/, /\/posts?([/?#.]|$)/, /\/press([/?#.]|$)/,
                /\/events?([/?#.]|$)/, /\/media([/?#.]|$)/,
                /\/careers?([/?#.]|$)/, /\/jobs?([/?#.]|$)/, /\/vacanc(?:y|ies)([/?#.]|$)/,
                /\/updates?([/?#.]|$)/, /\/internships?([/?#.]|$)/,
                /\.(pdf|doc|docx|zip)$/i  // Block document downloads
            ];
            
            // Filter out blocked URLs
            const allowedLinks = discoveredLinks.filter(link => {
                const url = link.url.toLowerCase();
                const isBlocked = blockPatterns.some(pattern => pattern.test(url));
                if (isBlocked) {
                    log.debug(`🚫 Blocking discovered link: ${link.url} (${link.text})`);
                    return false;
                }
                return true;
            });
            
            log.info(`✅ ${allowedLinks.length} links passed filtering (${discoveredLinks.length - allowedLinks.length} blocked)`);
            
            // Classify and prioritize allowed links
            const coreKeywords = ['about', 'contact', 'service', 'pricing', 'team', 'staff', 'location', 'office'];
            
            const prioritizedLinks = allowedLinks.map(link => {
                const url = link.url.toLowerCase();
                const text = link.text.toLowerCase();
                
                // Boost priority if URL or link text contains core keywords
                const isCorePage = coreKeywords.some(keyword => 
                    url.includes(keyword) || text.includes(keyword)
                );
                
                if (isCorePage) {
                    link.priority = 10;
                    link.label = 'CORE_PAGE';
                    log.info(`✨ High-priority page discovered: ${link.url} (${link.text})`);
                } else if (link.source === 'navigation') {
                    link.priority = 7;
                    link.label = 'NAV_PAGE';
                    log.info(`📌 Navigation page discovered: ${link.url} (${link.text})`);
                }
                
                return link;
            });

            // Enqueue discovered links with priorities
            if (prioritizedLinks.length > 0) {
                await enqueueLinks({
                    urls: prioritizedLinks.map(link => link.url),
                    transformRequestFunction: (req) => {
                        const matchingLink = prioritizedLinks.find(l => l.url === req.url);
                        if (matchingLink) {
                            req.userData = { 
                                ...req.userData, 
                                label: matchingLink.label || 'DISCOVERED',
                                linkText: matchingLink.text,
                                source: matchingLink.source
                            };
                            // @ts-ignore
                            req.priority = matchingLink.priority;
                        }
                        return req;
                    }
                });
            }
        }

        // 1. Extract content (Optimized for JavaScript-heavy sites)
        // Wait for network to be mostly idle AND for a reasonable delay
        try {
            await Promise.race([
                page.waitForLoadState('networkidle', { timeout: 5000 }),
                page.waitForTimeout(3000)  // Minimum 3s wait for JS to execute
            ]);
        } catch (e) {
            // If network doesn't settle, still wait minimum time
            await page.waitForTimeout(3000);
        }

        let cleanedText: string = await page.evaluate(`
            (function() {
                var remove = function(sel) {
                    document.querySelectorAll(sel).forEach(function(el) { el.remove(); });
                };
                remove('nav'); remove('header'); remove('footer'); 
                remove('script'); remove('style'); remove('noscript');
                remove('.nav'); remove('.header'); remove('.footer');
                remove('#nav'); remove('#header'); remove('#footer');
                remove('[role="navigation"]'); remove('[aria-label="cookie"]');
                return document.body.innerText.replace(/\\s+/g, ' ').trim().slice(0, 15000);
            })()
        `);

        // Short retry only if very sparse (<30 words)
        const wordCount = cleanedText.split(/\s+/).filter(w => w.length > 0).length;
        if (wordCount < 30) {
            log.warning(`Text very sparse (${wordCount} words). Short wait and re-extract.`);
            await page.waitForTimeout(1000);
            cleanedText = await page.evaluate(`
                (function() {
                    var remove = function(sel) {
                        document.querySelectorAll(sel).forEach(function(el) { el.remove(); });
                    };
                    remove('nav'); remove('header'); remove('footer'); 
                    remove('script'); remove('style'); remove('noscript');
                    remove('.nav'); remove('.header'); remove('.footer');
                    remove('#nav'); remove('#header'); remove('#footer');
                    remove('[role="navigation"]'); remove('[aria-label="cookie"]');
                    return document.body.innerText.replace(/\\s+/g, ' ').trim().slice(0, 15000);
                })()
            `);
        }

        // 2. Extract contacts
        // Fix: Allow spaces and dashes in phone numbers
        const mpesaRegex = /(?:Till|Paybill|Buy Goods|M-?Pesa).*?(\d{5,7})/gi;
        const phoneRegex = /(?:wa\.me|\+?254|0)?\s*(7[\d\s-]{8,})/g;
        const mpesaMatches = [...cleanedText.matchAll(mpesaRegex)].map(m => m[0]);
        const phoneMatches = [...cleanedText.matchAll(phoneRegex)].map(m => m[1] ? m[1].replace(/\D/g, '') : m[0].replace(/\D/g, ''));

        // 🧠 SMART STOP CHECK
        const serviceKeywords = [
            'consultation', 'treatment', 'surgery', 'diagnostics', // Medical
            'litigation', 'representation', 'defense', 'contract', // Legal
            'services', 'solutions', 'repair', 'maintenance'       // General
        ];

        const hasContact = phoneMatches.length > 0;
        const hasServices = serviceKeywords.filter(k => cleanedText.toLowerCase().includes(k)).length > 3;
        const highValueFound = hasContact && hasServices;

        if (highValueFound) {
            log.info(`🎯 High-Value Data Found (Phone + Services). Filtering for CORE pages only.`);
        }

        // 3. Enqueue additional links from current page (Smart Filtering with STRICT same-domain enforcement)
        await enqueueLinks({
            strategy: 'same-domain',
            globs: [`${new URL(startUrl).origin}/**`],  // ✅ Explicitly restrict to origin
            exclude: [
                // 🛑 Block unwanted patterns
                /\/blogs?([/?#.]|$)/, /\/insights?([/?#.]|$)/, /\/news([/?#.]|$)/,
                /\/articles?([/?#.]|$)/, /\/posts?([/?#.]|$)/, /\/press([/?#.]|$)/,
                /\/events?([/?#.]|$)/, /\/media([/?#.]|$)/, /\/legal([/?#.]|$)/,
                /\/privacy([/?#.]|$)/, /\/terms([/?#.]|$)/, /\/updates?([/?#.]|$)/,
                /\/disclaimer([/?#.]|$)/, /\/careers?([/?#.]|$)/, /\/jobs?([/?#.]|$)/,
                /\/vacanc(?:y|ies)([/?#.]|$)/, /\/internships?([/?#.]|$)/,
                /\.(jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|ppt|pptx|zip|tar|gz|css|js|ico|woff|svg|mp4|mp3|webp)$/i,
                /\/(cart|basket|checkout|login|signin|register|auth|account|admin|logout|password)/
            ],
            transformRequestFunction: (req) => {
                const url = req.url.toLowerCase();
                const reqOrigin = new URL(req.url).origin;
                const targetOrigin = new URL(startUrl).origin;

                // 🛡️ SAFETY: Double-check it's same origin
                if (reqOrigin !== targetOrigin) {
                    log.debug(`🚫 Blocking external URL: ${req.url}`);
                    return false;
                }

                // ✅ Priority Tagging for Core Pages (discovered during crawl)
                const coreKeywords = [
                    '/about', '/contact', '/location', '/us',
                    '/service', '/product', '/offering', '/solution',
                    '/pricing', '/rate', '/cost', '/plan',
                    '/team', '/staff', '/people', '/office'
                ];

                const isCorePage = coreKeywords.some(keyword => url.includes(keyword));

                if (isCorePage && !req.userData.label) {
                    req.userData = { ...req.userData, label: 'CORE_PAGE_DISCOVERED' };
                    // @ts-ignore
                    req.priority = 10;
                    log.debug(`🎯 Core page found during crawl: ${req.url}`);
                }

                // 🧠 SMART STOP: If we have high value data, ONLY allow core pages.
                if (highValueFound && !isCorePage && req.userData.label !== 'CORE_PAGE' && req.userData.label !== 'NAV_PAGE') {
                    log.debug(`⏸️ Smart Stop: Skipping non-core page ${req.url}`);
                    return false;
                }

                return req;
            }
        });

        // 4. Parallel metadata extraction
        const [description, keywords, ogImage] = await Promise.all([
            page.getAttribute('meta[name="description"]', 'content').catch(() => ''),
            page.getAttribute('meta[name="keywords"]', 'content').catch(() => ''),
            page.getAttribute('meta[property="og:image"]', 'content').catch(() => ''),
        ]);

        // 5. Save data
        await Dataset.pushData({
            url: request.url,
            title,
            description,
            keywords,
            ogImage,
            text: cleanedText,
            contacts: {
                phones: [...new Set(phoneMatches)],
                mpesa: [...new Set(mpesaMatches)]
            },
            type: 'page'
        });
    },

    failedRequestHandler: async ({ request, log }) => {
        log.error(`Request ${request.url} failed after all retries.`);
    },
}, config);

(async () => {
    try {
        console.error("Starting Crawler run...");
        await crawler.run([startUrl]);
        console.error("Crawler run finished.");

        const data = await Dataset.getData();
        console.log("__JSON_START__");
        console.log(JSON.stringify(data.items));
        console.log("__JSON_END__");
    } catch (error) {
        console.error("FATAL ERROR:");
        console.error(error);
        console.error(JSON.stringify({ error: error.message }));
        process.exit(1);
    }
})();
