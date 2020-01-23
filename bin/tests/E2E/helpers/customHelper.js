'use strict';
let Helper = codecept_helper;
const assert = require('assert');

class MyHelper extends Helper {

    async fillFieldInIFrame(iFrameSelector, fieldSelector, value, ...options) {
        const { page } = this.helpers.Puppeteer;
        const helper = this.helpers.Puppeteer;
        await page.waitForSelector(iFrameSelector);

        const elementHandle = await page.$(iFrameSelector);
        const frame = await elementHandle.contentFrame();

        await frame.type(fieldSelector, value, { delay: 20 });
    }

    /**
     * check if selector is visible if yes return true
     * @syntax I.getVisibility (selector)
     * @param selector element to find
     */
    async checkIfVisible(selector) {
        const helper = this.helpers['Puppeteer'];
        try {
            const numVisible = await helper.grabNumberOfVisibleElements(selector);

            return !!numVisible;
        } catch (err) {
            output.log('Skipping operation as element is not visible');
            return false;
        }
    }

    setValue(valName, value) {
        if(!this.REGISTER){
            this.REGISTER = {};
        }
        this.REGISTER[valName] = value;
    }

    getValue(valName) {
        return this.REGISTER[valName];
    }

    checkRegister(){
        return this.REGISTER;
    }
}

module.exports = MyHelper;