# JDB
PHP Powered Json Databases.

Have you ever had to store simple and small pieces of information on
disk in a file? And then, did you have to read and parse that data later?
Isn't it boring?

JDB makes this tedious task fun. Essentially, it serializes PHP objects
and arrays using json_encode and writes them to a file, and when needs
to retrieve the data, it decodes it using json_decode and provides a pleasant
interface to access, modify (iterate, filter, paginate etc.) and dump back all
of it to a file.

It has functions with the same names as those in the Laravel Collection
class. If you are familiar with Laravel, it's very easy to adapt to.

You can ask why don't we create an database driver to work with json files
on Laravel, chill and relax? Then I'll ask back what if we are not on Laravel?

### Creating Database
First, start by creating a database.

`/index.php`
```php
<?php

require_once 'lib/bootstrap.php';

use JDB\JDB;
use JDB\Exception\DatabaseExistsException;

try
{
  $usa = JDB::createDatabase( 'data/usa' );
}
catch( DatabaseExistsException $e )
{
  $usa = JDB::connect( 'data/usa' );
}
```

As a result of this process, `data/usa` is created in the same directory as the index.php file. You can think of each folder under the directory named `data` as a database. 

If you attempt to create a database that already exists, an exception will be thrown.

### Connecting A Database
If there is a database that you are sure exists, you can access it. We call this process ***connecting***.

```PHP
<?php

use JDB\Exception\DatabaseDoesntExistsException;

try
{
  $usa = JDB::connect( 'data/usa' );
}
catch( DatabaseDoesntExistsException $e )
{
  $usa = JDB::createDatabase( 'data/usa' );
}
```

If the database is not found, an exception is thrown.

### Checking Is A Database Exists
You can find out whether a database already exists before creating it, either by using the try-catch mechanism as above or by using a method.

```PHP
<?php

if( JDB::databaseExists( 'data/usa' ))
{
  $usa = JDB::connect( 'data/usa' );
}
else
{
  $usa = JDB::createDatabase( 'data/usa' );
}
```

Whether this code runs for the first time or the 1000th time, you can access the database every time.

### Listing Databases
You can get a list of all the databases you have created in a data repository.

```PHP
<?php

echo implode( ',', JDB::databases( 'data' ));
```

The output will be like:
```
usa, fr, tr
```

Please keep in mind that you can use multiple data repository if you want.

### Creating Table
Now you have a database. That means you can add tables in it.

```PHP
<?php

use JDB\Exception\TableExistsException;

try
{
  $states = $usa->createTable( 'states' );
}
catch( TableExistsException $e )
{
  $states = $usa->table( 'states' );
}
```

As a result of this process, two files are created in the `data/usa` directory.

`data/usa/states.json`
```JSON
[]
```

`data/usa/states.meta.json`
```JSON
{"current_id":0,"rows":0}
```

Your data will be stored in the first of these files. In the other, metadata such as the latest row number of the table and the total number of rows are stored.

You can consider each array item in the data file as a row.

### Accessing Table
Once you have created tables, you can now connect and perform operations on them.

```PHP
<?php

use JDB\Exception\TableDoesntExistsException;

try
{
  $states = $usa->table( 'states' );
}
catch( TableDoesntExistsException $e )
{
  $states = $usa->createTable( 'states' );
}
```

If the table is not found, an exception will be thrown.

### Checking A Table Exists
You can find out whether a table already exists before creating it, either by using the try-catch mechanism as above or by using a method.

```PHP
<?php

if( $usa::tableExists( 'usa' ))
{
  $states = $usa->table( 'states' );
}
else
{
  $states = $usa->createTable( 'states' );
}
```

### Inserting Data To Tables
#### Insert One Row At A Time

```php
<?php

$states->insert(
[
  "name" => "Arizona",
  "short" => "AZ"
]);

$states->insert(
[
  "name" => "Washington",
  "short" => "WA",
  "capital" => true
]);

$states->save();
```

`data/usa/states.json`
```JSON
[
  {"id":1,"name":"Arizona","short":"AZ"},
  {"id":2,"name":"Washington","short":"WA","capital":true}
]
```

`data/usa/states.meta.json`
```JSON
{"current_id":2,"rows":2}
```

#### Insert Multiple Rows At A Time
```PHP
<?php

$row1 = [
  "name" => "California",
  "short": => "CA"
];

$row2 = [
  "name" => "Texas",
  "short" => "TX"
];

$states
  ->insert( $row1, $row2 )
  ->save();
```

`data/usa/states.json`
```JSON
[
  {"id":1,"name":"Arizona","short":"AZ"},
  {"id":2,"name":"Washington","short":"WA","capital":true},
  {"id":3,"name":"California","short":"CA"},
  {"id":4,"name":"Texas","short":"TX"}
]
```

`data/usa/states.meta.json`
```JSON
{"current_id":4,"rows":4}
```

### Getting Rows Count
```PHP
<?php

echo $states->length;
```

```
4
```

### Iterating Rows
```PHP
<?php

$states->each( function( Row $state, int $index, Collection $collection )
{
  echo $state->name, ", ";
});
```

```
Arizona, Washington, California, Texas,
```

### Filtering Rows
```PHP
<?php

$capitalStates = fn( Row $state ) => $state->capital === true;

$states
  ->filter( $capitalStates )
  ->each( function( Row $capitalState )
  {
      echo $capitalState->name, ", ";
  });
```

```
Washington,
```

The filter method returns a new collection that contains only the elements matching the given conditions. However, if a Row object's `delete` method called then that Row will be removed from all the collections in the runtime and as well as json file. Please keep this rule in mind.

### Mapping Rows
```PHP
<?php

$states
  ->map( fn( Row $state ) =>
    $state->capital? "true" : "false"
  )
  ->each( function( bool $isCapital )
  {
    echo $isCapital, ", ";
  });
```

```
false, true, false, false, 
```

### Skipping Rows


### Manipulating Rows

Manipulations are performed in the server's memory. This means that unless explicitly stated to save to a file, they won't be saved to their file.

#### Updating Field Value
```PHP
<?php

$states
  ->filter( fn( Row $state ) =>
    $state->capital === null
  )
  ->each( function( Row $state )
  {
    $state->capital = false;
  });

$states->save();
```

> Please note that ***collections do not have*** a save method, but ***tables do have*** it!

`data/usa/states.json`
```JSON
[
  {"id":1,"name":"Arizona","short":"AZ","capital":false},
  {"id":2,"name":"Washington","short":"WA","capital":true},
  {"id":3,"name":"California","short":"CA","capital":false},
  {"id":4,"name":"Texas","short":"TX","capital":false}
]
```

#### Removing Field From Row
```PHP
<?php

$states
  ->filter( fn( Row $state ) =>
    $state->capital === false
  )
  ->each( function( Row $state )
  {
    $state->remove( "capital" );
    // or
    unset( $state->capital );
  });

$states->save();
```

`data/usa/states.json`
```JSON
[
  {"id":1,"name":"Arizona","short":"AZ"},
  {"id":2,"name":"Washington","short":"WA","capital":true},
  {"id":3,"name":"California","short":"CA"},
  {"id":4,"name":"Texas","short":"TX"}
]
```

#### Renaming Field Name
```PHP
<?php

$states
  ->filter( fn( Row $state ) =>
    $state->capital
  )
  ->each( fn( Row $state ) =>
    $state->rename( "capital", "is_capital" );
  );

$states->save();
```

`data/usa/states.json`
```JSON
[
  {"id":1,"name":"Arizona","short":"AZ"},
  {"id":2,"name":"Washington","short":"WA","is_capital":true},
  {"id":3,"name":"California","short":"CA"},
  {"id":4,"name":"Texas","short":"TX"}
]
```

#### Removing Row From Table
```PHP
<?php

$states
  ->filter( fn( Row $state ) =>
    $state->capital === null
  )
  ->each( fn( Row $state ) =>
    $state->delete()
  );

$states->save();
```

`data/usa/states.json`
```JSON
[
  {"id":2,"name":"Washington","short":"WA","is_capital":true},
]
```

`data/usa/states.meta.json`
```JSON
{"current_id":4,"rows":1}
```

