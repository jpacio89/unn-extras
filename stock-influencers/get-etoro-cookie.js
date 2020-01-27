require('chromedriver');
const { Builder, By, Key, until } = require('selenium-webdriver');
const chrome    = require('selenium-webdriver/chrome');

(async function example() {
var driver = new Builder()
                .forBrowser('chrome')
                .setChromeOptions(new chrome.Options().excludeSwitches('--enable-automation'))
                .build();
 // chromeCapabilities.set('chromeOptions', chromeOptions);
  try {
    // await driver.get('https://www.etoro.com');
    await driver.executeScript("window.key = \"blahblah\"");
    await driver.executeScript('window.navigator.webdriver = undefined')
    await driver.get('https://www.etoro.com/sapi/trade-data-real/history/public/credit/flat?CID=5987004&ItemsPerPage=500&PageNumber=1&StartTime=2019-01-25T23:00:00.000Z&client_request_id=3b4d9cab-06ec-4699-98d2-aa3f9290555c');
    await driver.sleep(150000);
    await driver.executeScript('return document.cookie').then((result) => {
        console.log(result);
    });

  } finally {
    await driver.quit();
  }
})();
