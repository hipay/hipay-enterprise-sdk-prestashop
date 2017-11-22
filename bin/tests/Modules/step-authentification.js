exports.proceed = function proceed(test) {
    /* Connection to prestashop admin panel */
    casper.thenOpen(baseURL + "/admin-hipay", function() {
        this.echo("Connecting to Prestashop admin panel...", "INFO");

        this.waitForSelector("#login_form", function success() {
            this.fillSelectors('form#login_form', {
                'input[name="email"]': admin_login,
                'input[name="passwd"]': admin_passwd
            }, false);
            this.click('.form-group button[type=submit]');
            this.waitForUrl(/AdminDashboard/, function success() {
                test.info("Connected");
            }, function fail() {
                test.assertExists(".error-msg", "Incorrect credentials !");
            }, 20000);
        }, function fail() {
            this.waitForUrl(/controller=/, function success() {
                test.info("Already logged to admin panel !");
            }, function fail() {
                test.assertUrlMatch(/controller=/, "Already connected");
            });
        },10000);

    });
};