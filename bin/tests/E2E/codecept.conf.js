exports.config = {
  output: './output',
  helpers: {
    Puppeteer: {
      url: 'http://localhost:8087',
      show: true
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
      './step_definitions/bo_hipay_steps.js',
      './step_definitions/global_steps.js'
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