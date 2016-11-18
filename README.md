# env ts [![Build Status](https://travis-ci.org/helhum/env-ts.svg?branch=master)](https://travis-ci.org/helhum/env-ts)

This is a composer plugin, that writes environment
variables to a TypoScript constants file.

This package is the missing link to be able to provide different TypoScript settings
for TYPO3 installations in different environments.

## configuration options

You configure env ts in the extra section of a `composer.json` in any package like that:

```
  "extra": {
      "helhum/env-ts": {
          "files": {
              "Configuration/TypoScript/Constants/environment.ts": [
                  "WEBEX_",
                  "INXMAIL_"
              ]
          }
          "prefix": "environment"
          "array-delimiter": "",
          "lower-camel-case": false
      }
    }
```

#### `files` [array]
Multiple files (path relative to package dir) can be specified as key and which environment variable prefixes should be included.
In the above example it would have been enough to specify the prefix `PAGE-`

#### `prefix` [string]
By default environment variables are passed as is to the constants file.
But it is possible to specify a prefix for the constants.
In the above example `environment.page.root` is written for env var `PAGE-ROOT`

*The default value* is `environment`

#### `array-delimiter` [string]
By default environment variables are passed as is to the constants file.
But it is possible to specify an array delimiter of the environment vars to be transformed into an
array part of the constants. In the above example `environment.page.root` is written for env var `PAGE-ROOT`

*The default value* is `-`

#### `lower-camel-case` [bool]
Whether the constant name should rather be lower camel cased than totally upper cased.
In the above example `environment.page.customerLogin` is written for env var `PAGE-CUSTOMER_LOGIN`

*The default value* is `true`.

## Feedback

Any feedback is appreciated. Please write bug reports, feature request, create pull requests, or just drop me a "thank you" via [Twitter](https://twitter.com/helhum) or spread the word.

Thank you!
