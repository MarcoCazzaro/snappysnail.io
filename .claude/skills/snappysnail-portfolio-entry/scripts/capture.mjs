#!/usr/bin/env node
// Screenshot capture for the snappysnail-portfolio-entry skill.
// Usage: node capture.mjs <url> <outfile> <width> <height> [--full-page]
//
// Runs inside this project's DDEV container (Node 24). Browsers live in the
// persistent ddev-global-cache mount so they survive container rebuilds —
// see PLAYWRIGHT_BROWSERS_PATH in package.json's "playwright:install" script.

import { chromium } from 'playwright';

const [, , url, outfile, width, height, ...rest] = process.argv;

if (!url || !outfile || !width || !height) {
    console.error('Usage: node capture.mjs <url> <outfile> <width> <height> [--full-page]');
    process.exit(1);
}

const fullPage = rest.includes('--full-page');

const browser = await chromium.launch();
const page = await browser.newPage({
    viewport: { width: Number(width), height: Number(height) },
    deviceScaleFactor: 2,
    ignoreHTTPSErrors: true,
});

try {
    await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
    await page.screenshot({ path: outfile, fullPage });
    console.log(`Saved ${outfile}`);
} finally {
    await browser.close();
}
