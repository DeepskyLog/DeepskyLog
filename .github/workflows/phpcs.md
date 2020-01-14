name: PHP CS Inspections
on: [push]

jobs:
  phpunit:
    name: PhpCS
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v1
    - uses: rtCamp/rtCamp/action-phpcs-inspection@master
