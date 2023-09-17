# JDB
PHP Powered Json Databases.

Have you ever had to store simple and small pieces of information on
disk in a file? And then, did you have to read and parse that data later?
Isn't it boring?

JDB makes this tedious task fun. Essentially, it serializes PHP objects
and arrays using json_encode and writes them to a file, and when it needs
to retrieve the data, it decodes it using json_decode and provides a pleasant
interface to access, modify (iterate, filter, paginate etc.) and dump back all
of it to a file.

It has functions with the same names as those in the Laravel Collection
class. If you are familiar with Laravel, it's very easy to adapt to.

You can ask why don't we create an database driver to work with json files
on Laravel, chill and relax? Then I'll ask back what if we are not on Laravel?

### Creating Database

```php
require_once "lib/JDB.php";

try
{
  // creates just "data/usa" path, you can consider paths as databases
  $database = JDB::createDatabase( "data/usa" );

  // creates *.json files you can consider them as tables
  $table = $database->createTable( "states" );
}
catch( DatabaseExistsException $e ){}
catch( TableExistsException $e ){}
```

This process results in the creation of two files.

`data/usa/states.json`
```JSON
[]
```

`data/usa/states.meta.json`
```JSON
{"current_id":0,"rows":0}
```

If you attempt to create a database or a table that already exists,
an exception will be thrown for both state.

### Accessing An Existing Table

```PHP
try
{
  $database = JDB::connect( "data/usa" );

  // accessing a table
  $states = $database->table( "states" );
}
catch( DatabaseDoesntExistsException $e ){}
catch( TableDoesntExistsException $e ){}
```

### Inserting Data To Tables
#### Insert One Row At A Time

```php
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
$states->insert(
  [
    "name" => "California",
    "short": => "CA"
  ],
  [
    "name" => "Texas",
    "short" => "TX"
  ]
);

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

`data/usa/states.meta.json`
```JSON
{"current_id":4,"rows":4}
```

### Iterating Rows
```PHP
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
$states
  ->filter( fn( Row $state, int $index, Collection $collection ) =>
      $state->capital === true
  )
  ->each( function( Row $capitalState )
  {
      echo $capitalState->name, ", ";
  });
```

```
Washington,
```

The filter method returns a new collection that contains only the elements matching the given conditions. However, if a Row object's `delete` method called then that Row will be removed from all collections. Please keep this rule in mind.

### Mapping Rows
```PHP
$states
  ->map( fn( Row $state, int $index, Collection $collection ) =>
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

### Manipulating Rows

Manipulations are performed in server memory. This means that unless explicitly stated to save to a file, they will not be saved to a file.

#### Updating Field Value
```PHP
$states
  ->filter( fn( Row $state ) => $state->capital === null )
  ->each( function( Row $state )
  {
    $state->capital = false;
  });

$states->save();
```

Please note that collections do not have a save method, but tables do have it!

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
$states
  ->filter( fn( Row $state ) => $state->capital === false )
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
$states
  ->filter( fn( Row $state ) => $state->capital )
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
$states
  ->filter( fn( Row $state ) =>
    ! $state->capital
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

