const puppeteer = require('puppeteer-core');
const path = require('path'); const fs = require('fs');
const sleep = ms => new Promise(r => setTimeout(r, ms));
const BASE='http://localhost:8080', OUT=path.join(__dirname,'screenshots');
const CHROME='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
(async () => {
  const browser = await puppeteer.launch({ executablePath: CHROME, headless: true, args:['--no-sandbox','--disable-setuid-sandbox'] });
  const pg = await browser.newPage();
  await pg.setViewport({ width:390, height:844, isMobile:true, deviceScaleFactor:2 });
  const client = await pg.createCDPSession();
  await client.send('Network.clearBrowserCookies');   // pastikan logged-out
  await pg.goto(`${BASE}/login`, { waitUntil:'networkidle2', timeout:20000 });
  await sleep(900);
  await pg.screenshot({ path: path.join(OUT,'mob_ats_login.png'), fullPage:false });
  console.log('✓ mob_ats_login (login form)');
  await browser.close();
})();
