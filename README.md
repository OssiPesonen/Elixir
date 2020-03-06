# Elixir

### PHP Query Builder

This is a **zero dependency fork** of the Doctrine DBAL Query Builder 2.7.

Basically a version without the **Connection dependency**, so no database connection is required or available via this library! This library produces a ready-to-use SQL clause for you with parameters.

You can easily install the package via composer:

    composer require ossipesonen/elixir

## How to use

The following documentation is almost a direct copy of Doctrine's documentation of the QueryBuilder.

You can create a builder by creating a new class instance:

    <?php
    use Elixir\QueryBuilder;

    $queryBuilder = QueryBuilder();
    
You can export any built SQL clause by calling `->print()` or by just prefixing the builder with `(string)`:

    <?php
    use Elixir\QueryBuilder;
   
    $qb = new QueryBuilder();

    echo $qb->select('u.id')
       ->distinct()
       ->from('users', 'u')
       ->print();
            
    // SELECT DISTINCT u.id FROM users u
                
    $queryBuilder
            ->select('id', 'name')
            ->from('users')
            ->where('email = ?')
            ->setParameter(0, $userInputEmail);
            
    echo (string)$queryBuilder;
    
    // SELECT DISTINCT u.id FROM users u
    
            
Security: Safely preventing SQL Injection
-----------------------------------------

It is important to understand how the query builder works in terms of
preventing SQL injection. Because SQL allows expressions in almost
every clause and position the QueryBuilder can only prevent
SQL injections for calls to the methods ``setFirstResult()`` and
``setMaxResults()``.

All other methods cannot distinguish between user- and developer input
and are therefore subject to the possibility of SQL injection.

To safely work with the QueryBuilder you should **NEVER** pass user
input to any of the methods of the QueryBuilder and use the placeholder
``?`` or ``:name`` syntax in combination with

``$queryBuilder->setParameter($placeholder, $value)`` instead:

    <?php

    $queryBuilder
        ->select('id', 'name')
        ->from('users')
        ->where('email = ?')
        ->setParameter(0, $userInputEmail)
        ->print();
        
    // Or named parameters    
    $queryBuilder
        ->select('id', 'name')
        ->from('users')
        ->where('email = :email')
        ->setParameter('email', $userInputEmail)
        ->print();
        
    
        
**Note:** The numerical parameters in the QueryBuilder start with index 0 instead of 1

Building a Query
----------------

The ``QueryBuilder`` supports building ``SELECT``,
``INSERT``, ``UPDATE`` and ``DELETE`` queries. Which sort of query you
are building depends on the methods you are using.

For ``SELECT`` queries you start with invoking the ``select()`` method

    <?php

    $queryBuilder
        ->select('id', 'name')
        ->from('users')
        ->print();

For ``INSERT``, ``UPDATE`` and ``DELETE`` queries you can pass the
table name into the ``insert($tableName)``, ``update($tableName)``
and ``delete($tableName)``:

    <?php

    $queryBuilder
        ->insert('users')
        ->print();

    $queryBuilder
        ->update('users')
        ->print();

    $queryBuilder
        ->delete('users')
        ->print();

You can convert a query builder to its SQL string representation
by calling ``$queryBuilder->getSQL()`` or casting the object to string.

DISTINCT-Clause
----------------

The ``SELECT`` statement can be specified with a ``DISTINCT`` clause:



    <?php

    $queryBuilder
        ->select('name')
        ->distinct()
        ->from('users')
        ->print();

WHERE-Clause
----------------

The ``SELECT``, ``UPDATE`` and ``DELETE`` types of queries allow where
clauses with the following API:



    <?php

    $queryBuilder
        ->select('id', 'name')
        ->from('users')
        ->where('email = ?')
        ->print();

Calling ``where()`` overwrites the previous clause and you can prevent
this by combining expressions with ``andWhere()`` and ``orWhere()`` methods.
You can alternatively use expressions to generate the where clause.

Table alias
----------------

The ``from()`` method takes an optional second parameter with which a table
alias can be specified.



    <?php

    $queryBuilder
        ->select('u.id', 'u.name')
        ->from('users', 'u')
        ->where('u.email = ?')
        ->print();

GROUP BY and HAVING Clause
--------------------------------

The ``SELECT`` statement can be specified with ``GROUP BY`` and ``HAVING`` clauses.
Using ``having()`` works exactly like using ``where()`` and there are
corresponding ``andHaving()`` and ``orHaving()`` methods to combine predicates.
For the ``GROUP BY`` you can use the methods ``groupBy()`` which replaces
previous expressions or ``addGroupBy()`` which adds to them:



    <?php
    $queryBuilder
        ->select('DATE(last_login) as date', 'COUNT(id) AS users')
        ->from('users')
        ->groupBy('DATE(last_login)')
        ->having('users > 10')
        ->print();

Join Clauses
----------------

For ``SELECT`` clauses you can generate different types of joins: ``INNER``,
``LEFT`` and ``RIGHT``. The ``RIGHT`` join is not portable across all platforms
(Sqlite for example does not support it).

A join always belongs to one part of the from clause. This is why you have to
specify the alias of the ``FROM`` part the join belongs to as the first
argument.

As a second and third argument you can then specify the name and alias of the
join-table and the fourth argument contains the ``ON`` clause.



    <?php
    $queryBuilder
        ->select('u.id', 'u.name', 'p.number')
        ->from('users', 'u')
        ->innerJoin('u', 'phonenumbers', 'p', 'u.id = p.user_id')
        ->print();

The method signature for ``join()``, ``innerJoin()``, ``leftJoin()`` and
``rightJoin()`` is the same. ``join()`` is a shorthand syntax for
``innerJoin()``.

Order-By Clause
----------------

The ``orderBy($sort, $order = null)`` method adds an expression to the ``ORDER
BY`` clause. Be aware that the optional ``$order`` parameter is not safe for
user input and accepts SQL expressions.



    <?php
    $queryBuilder
        ->select('id', 'name')
        ->from('users')
        ->orderBy('username', 'ASC')
        ->addOrderBy('last_login', 'ASC NULLS FIRST')
        ->print();

Use the ``addOrderBy`` method to add instead of replace the ``orderBy`` clause.

Limit Clause
----------------

Only a few database vendors have the ``LIMIT`` clause as known from MySQL,
but we support this functionality for all vendors using workarounds.
To use this functionality you have to call the methods ``setFirstResult($offset)``
to set the offset and ``setMaxResults($limit)`` to set the limit of results
returned.



    <?php
    $queryBuilder
        ->select('id', 'name')
        ->from('users')
        ->setFirstResult(10)
        ->setMaxResults(20)
        ->print();

VALUES Clause
----------------

For the ``INSERT`` clause setting the values for columns to insert can be
done with the ``values()`` method on the query builder:



    <?php

    $queryBuilder
        ->insert('users')
        ->values(
            array(
                'name' => '?',
                'password' => '?'
            )
        )
        ->setParameter(0, $username)
        ->setParameter(1, $password)
        ->print();
        
    // INSERT INTO users (name, password) VALUES (?, ?)

Each subsequent call to ``values()`` overwrites any previous set values.
Setting single values instead of all at once is also possible with the
``setValue()`` method:



    <?php

    $queryBuilder
        ->insert('users')
        ->setValue('name', '?')
        ->setValue('password', '?')
        ->setParameter(0, $username)
        ->setParameter(1, $password)
        ->print();
        
    // INSERT INTO users (name, password) VALUES (?, ?)

Of course you can also use both methods in combination:



    <?php

    $queryBuilder
        ->insert('users')
        ->values(
            array(
                'name' => '?'
            )
        )
        ->setParameter(0, $username)
        ->print();
        
    // INSERT INTO users (name) VALUES (?)

    if ($password) {
        $queryBuilder
            ->setValue('password', '?')
            ->setParameter(1, $password)
            ->print();
            
        // INSERT INTO users (name, password) VALUES (?, ?)
    }

Not setting any values at all will result in an empty insert statement:



    <?php

    $queryBuilder
        ->insert('users')
        ->print();
        
    // INSERT INTO users () VALUES ()

Set Clause
----------------

For the ``UPDATE`` clause setting columns to new values is necessary
and can be done with the ``set()`` method on the query builder.
Be aware that the second argument allows expressions and is not safe for
user-input:



    <?php

    $queryBuilder
        ->update('users', 'u')
        ->set('u.logins', 'u.logins + 1')
        ->set('u.last_login', '?')
        ->setParameter(0, $userInputLastLogin)
        ->print();

Building Expressions
--------------------

For more complex ``WHERE``, ``HAVING`` or other clauses you can use expressions
for building these query parts. You can invoke the expression API, by calling
``$queryBuilder->expr()`` and then invoking the helper method on it.

Most notably you can use expressions to build nested And-/Or statements:



    <?php

    $queryBuilder
        ->select('id', 'name')
        ->from('users')
        ->where(
            $queryBuilder->expr()->and(
                $queryBuilder->expr()->eq('username', '?'),
                $queryBuilder->expr()->eq('email', '?')
            )
        )
        ->print();

The ``and()`` and ``or()`` methods accept an arbitrary amount
of arguments and can be nested in each other.

There is a bunch of methods to create comparisons and other SQL snippets
on the Expression object that you can see on the API documentation.

Binding Parameters to Placeholders
----------------------------------

It is often not necessary to know about the exact placeholder names
during the building of a query. You can use ```setParameter```
to bind a value to a placeholder and directly use that placeholder
in your query as a return value:

    <?php

    $queryBuilder = new QueryBuilder();

    $queryBuilder->select('count(id) as count')
       ->from('sent_emails');
       ->where('sent_emails.email = :email')->setParameter('email', 'test@example.com');
       ->andWhere('sent_emails.sent > :start')->setParameter('start', date('Y-m-d H:i:s', strtotime('2020-03-06 12:00:00')));
       ->andWhere('sent_emails.sent < :end')->setParameter('end', date('Y-m-d H:i:s', strtotime('2020-03-06 16:00:00')));
       
    // SELECT count(id) as count FROM sent_emails WHERE (sent_emails.email = :email) AND (sent_emails.sent > :start) AND (sent_emails.sent < :end)
   
    // You can retrieve parameters as an associative array
    
    $queryBuilder->getParameters();
   
    /* {
     *     ["email"]=>
     *     string(16) "test@example.com"
     *     ["start"]=>
     *     string(19) "2020-03-06 12:00:00"
     *     ["end"]=>
     *     string(19) "2020-03-06 16:00:00"
     *   }
     */    
