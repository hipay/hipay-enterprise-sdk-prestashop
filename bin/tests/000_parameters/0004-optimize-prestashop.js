/**********************************************************************************************
 *
 *                       Activate option to improve prestashop
 *
 *
/**********************************************************************************************/
casper.test.begin('Activate cache for improve prestashop', function(test) {
    phantom.clearCookies();

    casper.start(baseURL)
    .then(function() {
        this.logToBackend();
    })
    .then(function(){
        this.echo("Activate cache to optimize response time","INFO");
        this.waitForSelector('ul.menu #subtab-AdminPerformance', function success() {
            this.click('ul.menu li#subtab-AdminAdvancedParameters > a');
            this.waitForText('Informations de configuration', function() {

                this.click('li#subtab-AdminPerformance > a');

                this.waitForSelector('input[name="cache_active"]', function success() {
                    this.fillSelectors("form#configuration_form", {
                            'input[name="cache_active"]' : 1,
                            'input[name="caching_system"]': "CacheMemcached",
                            'input[name="PS_CSS_THEME_CACHE"]': "1",
                            'input[name="PS_JS_THEME_CACHE"]': "1",
                            'input[name="PS_HTACCESS_CACHE_CONTROL"]': "1",
                        }, false
                    );
                    this.click('button[name="submitAddconfiguration"]');
                    this.waitForSelector('div.alert.alert-success', function success() {
                        test.info('Done');
                    }, function fail(){
                        test.assertExists('div.alert.alert-success','Update configuration success')
                    });
                }, function fail() {
                    test.assertExists('input[name="cache_active"]', "Cache exists");
                }, 15000);
            }, function fail (){
                test.assertTextExist('INFORMATIONS DE CONFIGURATION', "Configuration page exist");
            });
        }, function fail() {
            test.assertExists('ul.menu #subtab-AdminPerformance', "Performance page exists");
        }, 15000);
    })
    .run(function() {
        test.done();
    });
});
