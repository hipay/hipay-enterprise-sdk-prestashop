exports.config = {
    output: './output',
    helpers: {
        Puppeteer: {
            url: 'http://localhost:8087',
            browser: 'chrome',
            show: false,
            waitForNavigation: ['domcontentloaded', 'networkidle0'],
            chrome: {
                args: [
                    '--lang=ja,en-US,en',
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--window-size=1800,1000',
                    '--disable-features=site-per-process'
                ],
                ignoreHTTPSErrors: true,
                defaultViewport: {
                    width: 1800,
                    height: 1000
                }
            }
        },
        REST: {
            endpoint: "http://localhost:8087",
        },
        MyHelper: {
            require: './helpers/customHelper'
        }
    },
    include: {
        I: './steps_file.js'
    },
    mocha: {},
    bootstrap: null,
    teardown: null,
    hooks: [],
    gherkin: {
        features: './features/*.feature',
        steps: ['./step_definitions/admin_steps.js',
            './step_definitions/shop_steps.js',
            './step_definitions/payment_steps.js',
            './step_definitions/order_steps.js',
            './node_modules/@hipay/hipay-cypress-utils/step_definitions/bo_hipay_steps.js',
            './node_modules/@hipay/hipay-cypress-utils/step_definitions/paypal_payment_steps.js',
            './node_modules/@hipay/hipay-cypress-utils/step_definitions/utilities_steps.js'
        ]
    },
    plugins: {
        screenshotOnFail: {
            enabled: true
        },
        retryFailedStep: {
            enabled: true
        }
    },
    tests: './*_test.js',
    name: 'tests-codecept-gherkin'
}