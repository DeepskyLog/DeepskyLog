# Contribution Guidelines

**DeepskyLog** is a community effort built by amateur astronomers in their free time, so feel free to contribute in any way you can. Every contribution helps!

Here's what you can do to help:

+ Submit [pull requests](https://github.com/DeepskyLog/DeepskyLog/pulls) with new code to fix problems, or to add new functionality (see below for guidelines). Easy things to start with are labelled as [good first issue](https://github.com/DeepskyLog/DeepskyLog/labels/good%20first%20issue).
+ [Open issues](https://github.com/DeepskyLog/DeepskyLog/issues/new/choose) for things you want to see added or modified.
+ Be part of the discussion by helping out with [existing issues](https://github.com/DeepskyLog/DeepskyLog/issues) or talking on our [slack channel](https://deepskylog.slack.com/).
+ Work on the Deep-sky database. There are missing catalogs or problematic entries in the database that can be fixed. Comment on or create [an issue](https://github.com/DeepskyLog/DeepskyLog/issues) and we will help you to get started!
+ Write tests for DeepskyLog.
+ Help translating DeepskyLog in your own language (see below for guidelines).
+ Be part of the Social team! We have accounts on [twitter](https://twitter.com/deepskylog), [facebook](https://www.facebook.com/deepskylog) and [instagram](https://www.instagram.com/deepskylog.be/).
+ Help writing the documentation in our [wiki](https://github.com/DeepskyLog/DeepskyLog/wiki/Manual).
+ Whenever you have questions on how to help, do not hesitate to contact us. We can set up one-to-one sessions to get you started.
+ Please always be polite and helpfull. We are developing and maintaining DeepskyLog in our free time. See our [code of conduct](CODE_OF_CONDUCT.md).

## Setting up a development environment

+ See the [documentation on how to set up the development environment](documentation/Development.md)

## Code submission and pull request guidelines

+ Your code will be checked for clean code using [php codesniffer](https://github.com/squizlabs/PHP_CodeSniffer).
+ Test unit tests will also be executed automatically.
+ It is not mandatory but highly appreciated if you provide **test cases** and/or performance tests (we recommend using [phpunit](https://phpunit.de/)).
+ More information can be found in the [Development Tricks](documentation/Development&#32;tricks.md).

### Writing tests

+ Before writing any tests run `phpunit`. It will execute the existing tests and should run without errors.
+ Write tests in the **test** directory. If you have trouble writing a test, check out the existing tests.
+ Be sure to run `phpunit` after writing your test. It is going to run all tests .
+ Make a new pull request **only if all the tests are passing**.
+ The unit tests will be executed automatically when making a pull request. If one of the test fail, this will be reflected in the status of the pull request.

## Localisation / translation

At this moment, [DeepskyLog](https://www.deepskylog.org/) is translated in the following languages:

| Language | % complete |
| -------- | ---------- |
| English  | 100% |
| Dutch    | 100% |
| French   | 100% |
| German   | 100% |
| Spanish  | 100% |
| Swedish  | 100% |

If you spot a mistake in one of the translations, please let us know or change the problem yourself by adapting the messages.po file in [GitHub](https://github.com/DeepskyLog/DeepskyLog/tree/master/resources/lang/i18n/). You can easily edit the po files using an editor, for example [POEdit](https://poedit.net/).

If you are interested in adding a new language to [DeepskyLog](https://www.deepskylog.org), please contact us, then we can help to get you started.

We don't have any native Swedish and Spanish speakers in the DeepskyLog team, so most of the translation is done using google translate and bing. We really could use a native speaker to make the translation better!
