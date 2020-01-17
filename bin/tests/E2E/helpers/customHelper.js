'use strict';
let Helper = codecept_helper;

class MyHelper extends Helper {

    async clickIfVisible(selector, ...options) {
        const helper = this.helpers['Puppeteer'];
        try {
            const numVisible = await helper.grabNumberOfVisibleElements(selector);

            if (numVisible) {
                return helper.click(selector, ...options);
            }
        } catch (err) {
            helper.say('Skipping operation as element is not visible');
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