A demo of an immune real-value negative selection algorithm
===

Demonstrates how a population of detectors is created around a set of known "good" ("self") elements, matures
and then is tested against a set of random incoming antigens which might or might not be harmful.

Installation
---

1. Clone this repository.
2. If you wish to record the results into a database:
    1. Create a database.
    2. Create the `include/db_config.php` file using the provided sample with your own values.
    3. Create the database structure by importing the `include/db.sql` file.
3. Visit the web address.