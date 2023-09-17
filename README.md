# JDB
PHP Powered JSON Databases.

Have you ever had to store simple and small pieces of information on disk in a file? And then, did you have to read and parse that data later? Isn't it boring?

JDB makes this tedious task fun. Essentially, it serializes PHP objects and arrays using json_encode and writes them to a file, and when it needs to retrieve the data, decodes it using json_decode and provides a pleasant interface to access it.

It has functions with the same names as those in the Laravel Collection class. If you are familiar with Laravel, it's very easy to adapt to.
