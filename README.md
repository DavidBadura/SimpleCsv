# SimpleCsv

```csv
id;name
1;foo
2;bar
3;baz
```

```php
foreach (new CsvParser('example.csv', ';') as $row) {
  echo $row['id'] . ': ' . $row['name'];
}
```
