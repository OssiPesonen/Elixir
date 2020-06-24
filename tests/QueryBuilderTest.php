<?php

declare(strict_types=1);

namespace Tests\Elixir;

use Elixir\Exceptions\QueryException;
use Elixir\QueryBuilder;
use Elixir\ParameterType;
use MongoDB\Driver\Query;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase {
    public function testSimpleSelectWithoutFrom() : void
    {
        $qb = new QueryBuilder();

        $qb->select('some_function()');

        self::assertEquals('SELECT some_function()', (string) $qb);
    }

    public function testSimpleSelect() : void
    {
        $qb = new QueryBuilder();

        $qb->select('u.id')
           ->from('users', 'u');

        self::assertEquals('SELECT u.id FROM users u', (string) $qb);
    }

    public function testSimpleSelectWithDistinctWithPrint() : void
    {
        $qb = new QueryBuilder();

        $qb->select('u.id')
           ->distinct()
           ->from('users', 'u')
            ->print();

        self::assertEquals('SELECT DISTINCT u.id FROM users u', $qb);
    }

    public function testSimpleSelectWithDistinct() : void
    {
        $qb = new QueryBuilder();

        $qb->select('u.id')
           ->distinct()
           ->from('users', 'u');

        self::assertEquals('SELECT DISTINCT u.id FROM users u', (string) $qb);
    }

    public function testSelectWithSimpleWhere() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.id')
           ->from('users', 'u')
           ->where($expr->andX($expr->eq('u.nickname', '?')));

        self::assertEquals('SELECT u.id FROM users u WHERE u.nickname = ?', (string) $qb);
    }

    public function testSelectWithLeftJoin() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->leftJoin('u', 'phones', 'p', $expr->eq('p.user_id', 'u.id'));

        self::assertEquals('SELECT u.*, p.* FROM users u LEFT JOIN phones p ON p.user_id = u.id', (string) $qb);
    }

    public function testSelectWithJoin() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->Join('u', 'phones', 'p', $expr->eq('p.user_id', 'u.id'));

        self::assertEquals('SELECT u.*, p.* FROM users u INNER JOIN phones p ON p.user_id = u.id', (string) $qb);
    }

    public function testSelectWithInnerJoin() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->innerJoin('u', 'phones', 'p', $expr->eq('p.user_id', 'u.id'));

        self::assertEquals('SELECT u.*, p.* FROM users u INNER JOIN phones p ON p.user_id = u.id', (string) $qb);
    }

    public function testSelectWithRightJoin() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->rightJoin('u', 'phones', 'p', $expr->eq('p.user_id', 'u.id'));

        self::assertEquals('SELECT u.*, p.* FROM users u RIGHT JOIN phones p ON p.user_id = u.id', (string) $qb);
    }

    public function testSelectWithAndWhereConditions() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->where('u.username = ?')
           ->andWhere('u.name = ?');

        self::assertEquals('SELECT u.*, p.* FROM users u WHERE (u.username = ?) AND (u.name = ?)', (string) $qb);
    }

    public function testSelectWithOrWhereConditions() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->where('u.username = ?')
           ->orWhere('u.name = ?');

        self::assertEquals('SELECT u.*, p.* FROM users u WHERE (u.username = ?) OR (u.name = ?)', (string) $qb);
    }

    public function testSelectWithOrOrWhereConditions() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->orWhere('u.username = ?')
           ->orWhere('u.name = ?');

        self::assertEquals('SELECT u.*, p.* FROM users u WHERE (u.username = ?) OR (u.name = ?)', (string) $qb);
    }

    public function testSelectWithAndOrWhereConditions() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->where('u.username = ?')
           ->andWhere('u.username = ?')
           ->orWhere('u.name = ?')
           ->andWhere('u.name = ?');

        self::assertEquals('SELECT u.*, p.* FROM users u WHERE (((u.username = ?) AND (u.username = ?)) OR (u.name = ?)) AND (u.name = ?)', (string) $qb);
    }

    public function testSelectGroupBy() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->groupBy('u.id');

        self::assertEquals('SELECT u.*, p.* FROM users u GROUP BY u.id', (string) $qb);
    }

    public function testSelectEmptyGroupBy() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->groupBy([])
           ->from('users', 'u');

        self::assertEquals('SELECT u.*, p.* FROM users u', (string) $qb);
    }

    public function testSelectEmptyAddGroupBy() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->addGroupBy([])
           ->from('users', 'u');

        self::assertEquals('SELECT u.*, p.* FROM users u', (string) $qb);
    }

    public function testSelectAddGroupBy() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->groupBy('u.id')
           ->addGroupBy('u.foo');

        self::assertEquals('SELECT u.*, p.* FROM users u GROUP BY u.id, u.foo', (string) $qb);
    }

    public function testSelectAddGroupBys() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->groupBy('u.id')
           ->addGroupBy('u.foo', 'u.bar');

        self::assertEquals('SELECT u.*, p.* FROM users u GROUP BY u.id, u.foo, u.bar', (string) $qb);
    }

    public function testSelectHaving() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->groupBy('u.id')
           ->having('u.name = ?');

        self::assertEquals('SELECT u.*, p.* FROM users u GROUP BY u.id HAVING u.name = ?', (string) $qb);
    }

    public function testSelectAndHaving() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->groupBy('u.id')
           ->andHaving('u.name = ?');

        self::assertEquals('SELECT u.*, p.* FROM users u GROUP BY u.id HAVING u.name = ?', (string) $qb);
    }

    public function testSelectHavingAndHaving() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->groupBy('u.id')
           ->having('u.name = ?')
           ->andHaving('u.username = ?');

        self::assertEquals('SELECT u.*, p.* FROM users u GROUP BY u.id HAVING (u.name = ?) AND (u.username = ?)', (string) $qb);
    }

    public function testSelectHavingOrHaving() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->groupBy('u.id')
           ->having('u.name = ?')
           ->orHaving('u.username = ?');

        self::assertEquals('SELECT u.*, p.* FROM users u GROUP BY u.id HAVING (u.name = ?) OR (u.username = ?)', (string) $qb);
    }

    public function testSelectOrHavingOrHaving() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->groupBy('u.id')
           ->orHaving('u.name = ?')
           ->orHaving('u.username = ?');

        self::assertEquals('SELECT u.*, p.* FROM users u GROUP BY u.id HAVING (u.name = ?) OR (u.username = ?)', (string) $qb);
    }

    public function testSelectHavingAndOrHaving() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->groupBy('u.id')
           ->having('u.name = ?')
           ->orHaving('u.username = ?')
           ->andHaving('u.username = ?');

        self::assertEquals('SELECT u.*, p.* FROM users u GROUP BY u.id HAVING ((u.name = ?) OR (u.username = ?)) AND (u.username = ?)', (string) $qb);
    }

    public function testSelectOrderBy() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->orderBy('u.name');

        self::assertEquals('SELECT u.*, p.* FROM users u ORDER BY u.name ASC', (string) $qb);
    }

    public function testSelectAddOrderBy() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->orderBy('u.name')
           ->addOrderBy('u.username', 'DESC');

        self::assertEquals('SELECT u.*, p.* FROM users u ORDER BY u.name ASC, u.username DESC', (string) $qb);
    }

    public function testSelectAddAddOrderBy() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
           ->from('users', 'u')
           ->addOrderBy('u.name')
           ->addOrderBy('u.username', 'DESC');

        self::assertEquals('SELECT u.*, p.* FROM users u ORDER BY u.name ASC, u.username DESC', (string) $qb);
    }

    public function testSelectAddAddOrderByTwice() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*', 'p.*')
            ->from('users', 'u')
            ->orderBy('u.name')
            ->orderBy('u.username', 'DESC');

        self::assertEquals('SELECT u.*, p.* FROM users u ORDER BY u.username DESC', (string) $qb);
    }

    public function testEmptySelect() : void
    {
        $qb  = new QueryBuilder();
        $qb2 = $qb->select();

        self::assertSame($qb, $qb2);
        self::assertEquals(QueryBuilder::SELECT, $qb->getType());
    }

    public function testSelectAddSelect() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*')
           ->addSelect('p.*')
           ->from('users', 'u');

        self::assertEquals('SELECT u.*, p.* FROM users u', (string) $qb);
    }

    public function testEmptyAddSelect() : void
    {
        $qb  = new QueryBuilder();
        $qb2 = $qb->addSelect();

        self::assertSame($qb, $qb2);
        self::assertEquals(QueryBuilder::SELECT, $qb->getType());
    }

    public function testSelectMultipleFrom() : void
    {
        $qb   = new QueryBuilder();
        $expr = $qb->expr();

        $qb->select('u.*')
           ->addSelect('p.*')
           ->from('users', 'u')
           ->from('phonenumbers', 'p');

        self::assertEquals('SELECT u.*, p.* FROM users u, phonenumbers p', (string) $qb);
    }

    public function testUpdate() : void
    {
        $qb = new QueryBuilder();
        $qb->update('users', 'u')
           ->set('u.foo', '?')
           ->set('u.bar', '?');

        self::assertEquals(QueryBuilder::UPDATE, $qb->getType());
        self::assertEquals('UPDATE users u SET u.foo = ?, u.bar = ?', (string) $qb);
    }

    public function testUpdateWithoutAlias() : void
    {
        $qb = new QueryBuilder();
        $qb->update('users')
           ->set('foo', '?')
           ->set('bar', '?');

        self::assertEquals('UPDATE users SET foo = ?, bar = ?', (string) $qb);
    }

    public function testUpdateWithMatchingAlias() : void
    {
        $qb = new QueryBuilder();
        $qb->update('users', 'users')
           ->set('foo', '?')
           ->set('bar', '?');

        self::assertEquals('UPDATE users SET foo = ?, bar = ?', (string) $qb);
    }

    public function testUpdateWhere() : void
    {
        $qb = new QueryBuilder();
        $qb->update('users', 'u')
           ->set('u.foo', '?')
           ->where('u.foo = ?');

        self::assertEquals('UPDATE users u SET u.foo = ? WHERE u.foo = ?', (string) $qb);
    }

    public function testEmptyUpdate() : void
    {
        $qb  = new QueryBuilder();
        $qb2 = $qb->update();

        self::assertEquals(QueryBuilder::UPDATE, $qb->getType());
        self::assertSame($qb2, $qb);
    }

    public function testDelete() : void
    {
        $qb = new QueryBuilder();
        $qb->delete('users', 'u');

        self::assertEquals(QueryBuilder::DELETE, $qb->getType());
        self::assertEquals('DELETE FROM users u', (string) $qb);
    }

    public function testDeleteWithoutAlias() : void
    {
        $qb = new QueryBuilder();
        $qb->delete('users');

        self::assertEquals(QueryBuilder::DELETE, $qb->getType());
        self::assertEquals('DELETE FROM users', (string) $qb);
    }

    public function testDeleteWithMatchingAlias() : void
    {
        $qb = new QueryBuilder();
        $qb->delete('users', 'users');

        self::assertEquals(QueryBuilder::DELETE, $qb->getType());
        self::assertEquals('DELETE FROM users', (string) $qb);
    }

    public function testDeleteWhere() : void
    {
        $qb = new QueryBuilder();
        $qb->delete('users', 'u')
           ->where('u.foo = ?');

        self::assertEquals('DELETE FROM users u WHERE u.foo = ?', (string) $qb);
    }

    public function testEmptyDelete() : void
    {
        $qb  = new QueryBuilder();
        $qb2 = $qb->delete();

        self::assertEquals(QueryBuilder::DELETE, $qb->getType());
        self::assertSame($qb2, $qb);
    }

    public function testInsertValues() : void
    {
        $qb = new QueryBuilder();
        $qb->insert('users')
           ->values(
               [
                   'foo' => '?',
                   'bar' => '?',
               ]
           );

        self::assertEquals(QueryBuilder::INSERT, $qb->getType());
        self::assertEquals('INSERT INTO users (foo, bar) VALUES(?, ?)', (string) $qb);
    }

    public function testInsertReplaceValues() : void
    {
        $qb = new QueryBuilder();
        $qb->insert('users')
           ->values(
               [
                   'foo' => '?',
                   'bar' => '?',
               ]
           )
           ->values(
               [
                   'bar' => '?',
                   'foo' => '?',
               ]
           );

        self::assertEquals(QueryBuilder::INSERT, $qb->getType());
        self::assertEquals('INSERT INTO users (bar, foo) VALUES(?, ?)', (string) $qb);
    }

    public function testInsertSetValue() : void
    {
        $qb = new QueryBuilder();
        $qb->insert('users')
           ->setValue('foo', 'bar')
           ->setValue('bar', '?')
           ->setValue('foo', '?');

        self::assertEquals(QueryBuilder::INSERT, $qb->getType());
        self::assertEquals('INSERT INTO users (foo, bar) VALUES(?, ?)', (string) $qb);
    }

    public function testInsertValuesSetValue() : void
    {
        $qb = new QueryBuilder();
        $qb->insert('users')
           ->values(
               ['foo' => '?']
           )
           ->setValue('bar', '?');

        self::assertEquals(QueryBuilder::INSERT, $qb->getType());
        self::assertEquals('INSERT INTO users (foo, bar) VALUES(?, ?)', (string) $qb);
    }

    public function testEmptyInsert() : void
    {
        $qb  = new QueryBuilder();
        $qb2 = $qb->insert();

        self::assertEquals(QueryBuilder::INSERT, $qb->getType());
        self::assertSame($qb2, $qb);
    }

    public function testGetState() : void
    {
        $qb = new QueryBuilder();

        self::assertEquals(QueryBuilder::STATE_CLEAN, $qb->getState());

        $qb->select('u.*')->from('users', 'u');

        self::assertEquals(QueryBuilder::STATE_DIRTY, $qb->getState());

        $sql1 = $qb->getSQL();

        self::assertEquals(QueryBuilder::STATE_CLEAN, $qb->getState());
        self::assertEquals($sql1, $qb->getSQL());
    }

    public function testSetMaxResults() : void
    {
        $qb = new QueryBuilder();
        $qb->setMaxResults(10);

        self::assertEquals(QueryBuilder::STATE_DIRTY, $qb->getState());
        self::assertEquals(10, $qb->getMaxResults());
    }

    public function testSetFirstResult() : void
    {
        $qb = new QueryBuilder();
        $qb->setFirstResult(10);

        self::assertEquals(QueryBuilder::STATE_DIRTY, $qb->getState());
        self::assertEquals(10, $qb->getFirstResult());
    }

    public function testResetQueryPart() : void
    {
        $qb = new QueryBuilder();

        $qb->select('u.*')->from('users', 'u')->where('u.name = ?');

        self::assertEquals('SELECT u.* FROM users u WHERE u.name = ?', (string) $qb);
        $qb->resetQueryPart('where');
        self::assertEquals('SELECT u.* FROM users u', (string) $qb);
    }

    public function testResetQueryParts() : void
    {
        $qb = new QueryBuilder();

        $qb->select('u.*')->from('users', 'u')->where('u.name = ?')->orderBy('u.name');

        self::assertEquals('SELECT u.* FROM users u WHERE u.name = ? ORDER BY u.name ASC', (string) $qb);
        $qb->resetQueryParts(['where', 'orderBy']);
        self::assertEquals('SELECT u.* FROM users u', (string) $qb);
    }

    public function testCreateNamedParameter() : void
    {
        $qb = new QueryBuilder();

        $qb->select('u.*')->from('users', 'u')->where(
            $qb->expr()->eq('u.name', $qb->createNamedParameter(10, ParameterType::INTEGER))
        );

        self::assertEquals('SELECT u.* FROM users u WHERE u.name = :dcValue1', (string) $qb);
        self::assertEquals(10, $qb->getParameter('dcValue1'));
        self::assertEquals(ParameterType::INTEGER, $qb->getParameterType('dcValue1'));
    }

    public function testCreateNamedParameterCustomPlaceholder() : void
    {
        $qb = new QueryBuilder();

        $qb->select('u.*')->from('users', 'u')->where(
            $qb->expr()->eq('u.name', $qb->createNamedParameter(10, ParameterType::INTEGER, ':test'))
        );

        self::assertEquals('SELECT u.* FROM users u WHERE u.name = :test', (string) $qb);
        self::assertEquals(10, $qb->getParameter('test'));
        self::assertEquals(ParameterType::INTEGER, $qb->getParameterType('test'));
    }

    public function testCreatePositionalParameter() : void
    {
        $qb = new QueryBuilder();

        $qb->select('u.*')->from('users', 'u')->where(
            $qb->expr()->eq('u.name', $qb->createPositionalParameter(10, ParameterType::INTEGER))
        );

        self::assertEquals('SELECT u.* FROM users u WHERE u.name = ?', (string) $qb);
        self::assertEquals(10, $qb->getParameter(1));
        self::assertEquals(ParameterType::INTEGER, $qb->getParameterType(1));
    }

    public function testReferenceJoinFromJoin() : void
    {
        $qb = new QueryBuilder();

        $qb->select('COUNT(DISTINCT news.id)')
           ->from('cb_newspages', 'news')
           ->innerJoin('news', 'nodeversion', 'nv', 'nv.refId = news.id AND nv.refEntityname=\'News\'')
           ->innerJoin('invalid', 'nodetranslation', 'nt', 'nv.nodetranslation = nt.id')
           ->innerJoin('nt', 'node', 'n', 'nt.node = n.id')
           ->where('nt.lang = :lang AND n.deleted != 1');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The given alias 'invalid' is not part of any FROM or JOIN clause table. The currently registered aliases are: news, nv.");
        self::assertEquals('', $qb->getSQL());
    }

    public function testSelectFromMasterWithWhereOnJoinedTables() : void
    {
        $qb = new QueryBuilder();

        $qb->select('COUNT(DISTINCT news.id)')
           ->from('newspages', 'news')
           ->innerJoin('news', 'nodeversion', 'nv', "nv.refId = news.id AND nv.refEntityname='Entity\\News'")
           ->innerJoin('nv', 'nodetranslation', 'nt', 'nv.nodetranslation = nt.id')
           ->innerJoin('nt', 'node', 'n', 'nt.node = n.id')
           ->where('nt.lang = ?')
           ->andWhere('n.deleted = 0');

        self::assertEquals("SELECT COUNT(DISTINCT news.id) FROM newspages news INNER JOIN nodeversion nv ON nv.refId = news.id AND nv.refEntityname='Entity\\News' INNER JOIN nodetranslation nt ON nv.nodetranslation = nt.id INNER JOIN node n ON nt.node = n.id WHERE (nt.lang = ?) AND (n.deleted = 0)", $qb->getSQL());
    }

    public function testSelectWithMultipleFromAndJoins() : void
    {
        $qb = new QueryBuilder();

        $qb->select('DISTINCT u.id')
           ->from('users', 'u')
           ->from('articles', 'a')
           ->innerJoin('u', 'permissions', 'p', 'p.user_id = u.id')
           ->innerJoin('a', 'comments', 'c', 'c.article_id = a.id')
           ->where('u.id = a.user_id')
           ->andWhere('p.read = 1');

        self::assertEquals('SELECT DISTINCT u.id FROM users u INNER JOIN permissions p ON p.user_id = u.id, articles a INNER JOIN comments c ON c.article_id = a.id WHERE (u.id = a.user_id) AND (p.read = 1)', $qb->getSQL());
    }

    public function testSelectWithJoinsWithMultipleOnConditionsParseOrder() : void
    {
        $qb = new QueryBuilder();

        $qb->select('a.id')
           ->from('table_a', 'a')
           ->join('a', 'table_b', 'b', 'a.fk_b = b.id')
           ->join('b', 'table_c', 'c', 'c.fk_b = b.id AND b.language = ?')
           ->join('a', 'table_d', 'd', 'a.fk_d = d.id')
           ->join('c', 'table_e', 'e', 'e.fk_c = c.id AND e.fk_d = d.id');

        self::assertEquals(
            'SELECT a.id ' .
            'FROM table_a a ' .
            'INNER JOIN table_b b ON a.fk_b = b.id ' .
            'INNER JOIN table_d d ON a.fk_d = d.id ' .
            'INNER JOIN table_c c ON c.fk_b = b.id AND b.language = ? ' .
            'INNER JOIN table_e e ON e.fk_c = c.id AND e.fk_d = d.id',
            (string) $qb
        );
    }

    public function testSelectWithMultipleFromsAndJoinsWithMultipleOnConditionsParseOrder() : void
    {
        $qb = new QueryBuilder();

        $qb->select('a.id')
           ->from('table_a', 'a')
           ->from('table_f', 'f')
           ->join('a', 'table_b', 'b', 'a.fk_b = b.id')
           ->join('b', 'table_c', 'c', 'c.fk_b = b.id AND b.language = ?')
           ->join('a', 'table_d', 'd', 'a.fk_d = d.id')
           ->join('c', 'table_e', 'e', 'e.fk_c = c.id AND e.fk_d = d.id')
           ->join('f', 'table_g', 'g', 'f.fk_g = g.id');

        self::assertEquals(
            'SELECT a.id ' .
            'FROM table_a a ' .
            'INNER JOIN table_b b ON a.fk_b = b.id ' .
            'INNER JOIN table_d d ON a.fk_d = d.id ' .
            'INNER JOIN table_c c ON c.fk_b = b.id AND b.language = ? ' .
            'INNER JOIN table_e e ON e.fk_c = c.id AND e.fk_d = d.id, ' .
            'table_f f ' .
            'INNER JOIN table_g g ON f.fk_g = g.id',
            (string) $qb
        );
    }

    public function testClone() : void
    {
        $qb = new QueryBuilder();

        $qb->select('u.id')
           ->from('users', 'u')
           ->where('u.id = :test');

        $qb->setParameter(':test', (object) 1);

        $qb_clone = clone $qb;

        self::assertEquals((string) $qb, (string) $qb_clone);

        $qb->andWhere('u.id = 1');

        self::assertNotSame($qb->getQueryParts(), $qb_clone->getQueryParts());
        self::assertNotSame($qb->getParameters(), $qb_clone->getParameters());
    }

    public function testSimpleSelectWithoutTableAlias() : void
    {
        $qb = new QueryBuilder();

        $qb->select('id')
           ->from('users');

        self::assertEquals('SELECT id FROM users', (string) $qb);
    }

    public function testParameters() {
        $qb = new QueryBuilder();

        $qb->select('count(id) as count')
           ->from('sent_emails');

        $qb->where('sent_emails.email = :email')->setParameter('email', 'test@example.com');
        $qb->andWhere('sent_emails.sent > :start')->setParameter('start', date('Y-m-d H:i:s', strtotime('2020-03-06 12:00:00')));
        $qb->andWhere('sent_emails.sent < :end')->setParameter('end', date('Y-m-d H:i:s', strtotime('2020-03-06 16:00:00')));

        $this->assertEquals("SELECT count(id) as count FROM sent_emails WHERE (sent_emails.email = :email) AND (sent_emails.sent > :start) AND (sent_emails.sent < :end)", (string)$qb);

        $parameters = $qb->getParameters();

        $this->assertEquals('test@example.com', $parameters['email']);
        $this->assertEquals('2020-03-06 12:00:00', $parameters['start']);
        $this->assertEquals('2020-03-06 16:00:00', $parameters['end']);
    }

    public function testSimpleSelectWithMatchingTableAlias() : void
    {
        $qb = new QueryBuilder();

        $qb->select('id')
           ->from('users', 'users');

        self::assertEquals('SELECT id FROM users', (string) $qb);
    }

    public function testSelectWithSimpleWhereWithoutTableAlias() : void
    {
        $qb = new QueryBuilder();

        $qb->select('id', 'name')
           ->from('users')
           ->where('awesome=9001');

        self::assertEquals('SELECT id, name FROM users WHERE awesome=9001', (string) $qb);
    }

    public function testComplexSelectWithoutTableAliases() : void
    {
        $qb = new QueryBuilder();

        $qb->select('DISTINCT users.id')
           ->from('users')
           ->from('articles')
           ->innerJoin('users', 'permissions', 'p', 'p.user_id = users.id')
           ->innerJoin('articles', 'comments', 'c', 'c.article_id = articles.id')
           ->where('users.id = articles.user_id')
           ->andWhere('p.read = 1');

        self::assertEquals('SELECT DISTINCT users.id FROM users INNER JOIN permissions p ON p.user_id = users.id, articles INNER JOIN comments c ON c.article_id = articles.id WHERE (users.id = articles.user_id) AND (p.read = 1)', $qb->getSQL());
    }

    public function testComplexSelectWithSomeTableAliases() : void
    {
        $qb = new QueryBuilder();

        $qb->select('u.id')
           ->from('users', 'u')
           ->from('articles')
           ->innerJoin('u', 'permissions', 'p', 'p.user_id = u.id')
           ->innerJoin('articles', 'comments', 'c', 'c.article_id = articles.id');

        self::assertEquals('SELECT u.id FROM users u INNER JOIN permissions p ON p.user_id = u.id, articles INNER JOIN comments c ON c.article_id = articles.id', $qb->getSQL());
    }

    public function testSelectAllFromTableWithoutTableAlias() : void
    {
        $qb = new QueryBuilder();

        $qb->select('users.*')
           ->from('users');

        self::assertEquals('SELECT users.* FROM users', (string) $qb);
    }

    public function testSelectAllWithoutTableAlias() : void
    {
        $qb = new QueryBuilder();

        $qb->select('*')
           ->from('users');

        self::assertEquals('SELECT * FROM users', (string) $qb);
    }

    public function testGetParameterType() : void
    {
        $qb = new QueryBuilder();

        $qb->select('*')->from('users');

        self::assertNull($qb->getParameterType('name'));

        $qb->where('name = :name');
        $qb->setParameter('name', 'foo');

        self::assertNull($qb->getParameterType('name'));

        $qb->setParameter('name', 'foo', ParameterType::STRING);

        self::assertSame(ParameterType::STRING, $qb->getParameterType('name'));
    }

    public function testGetParameterTypes() : void
    {
        $qb = new QueryBuilder();

        $qb->select('*')->from('users');

        self::assertSame([], $qb->getParameterTypes());

        $qb->where('name = :name');
        $qb->setParameter('name', 'foo');

        self::assertSame([], $qb->getParameterTypes());

        $qb->setParameter('name', 'foo', ParameterType::STRING);

        $qb->where('is_active = :isActive');
        $qb->setParameter('isActive', true, ParameterType::BOOLEAN);

        self::assertSame([
            'name'     => ParameterType::STRING,
            'isActive' => ParameterType::BOOLEAN,
        ], $qb->getParameterTypes());
    }

    public function testJoinWithNonUniqueAliasThrowsException() : void
    {
        $qb = new QueryBuilder();

        $qb->select('a.id')
           ->from('table_a', 'a')
           ->join('a', 'table_b', 'a', 'a.fk_b = a.id');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The given alias 'a' is not unique in FROM and JOIN clause table. The currently registered aliases are: a.");

        $qb->getSQL();
    }

    public function testWhereChainingWithAndWhereAsFirst(): void
    {
        $qb = new QueryBuilder();

        $qb->select('id')
           ->from('users')
           ->andWhere('id = :id');

        $this->assertEquals("SELECT id FROM users WHERE (1 = 1) AND (id = :id)", (string)$qb);
    }

    public function testWrongfulWhereChainingWithTwoWheres(): void
    {
        $qb = new QueryBuilder();

        $qb->select('id')
           ->from('users')
           ->where('id = :id')
           ->setParameter('id', 1)
           ->where('name = :name')
           ->setParameter('name', 'John Doe');

        $this->assertEquals("SELECT id FROM users WHERE (id = :id) AND (name = :name)", (string)$qb);
    }


    public function testWrongfulWhereChainingWithTwoAndWheres(): void
    {
        $qb = new QueryBuilder();

        $qb->select('id')
            ->from('users')
            ->andWhere('id = :id')
            ->setParameter('id', 1)
            ->andWhere('name = :name')
            ->setParameter('name', 'John Doe');

        $this->assertEquals("SELECT id FROM users WHERE (1 = 1) AND (id = :id) AND (name = :name)", (string)$qb);
    }

    public function testJoinWithoutAlias(): void
    {
        $qb = new QueryBuilder();

        $qb->select('user.id')
            ->from('user')
            ->join('user','addresses','','addresses.user_id = user.id');

        $this->assertEquals("SELECT user.id FROM user INNER JOIN addresses ON addresses.user_id = user.id", (string)$qb);
    }

    public function testInnerJoinWithoutAlias(): void
    {
        $qb = new QueryBuilder();

        $qb->select('user.id')
            ->from('user')
            ->innerJoin('user','addresses','addresses','addresses.user_id = user.id');

        $this->assertEquals("SELECT user.id FROM user INNER JOIN addresses ON addresses.user_id = user.id", (string)$qb);
    }

    public function testLeftJoinWithoutAlias(): void
    {
        $qb = new QueryBuilder();

        $qb->select('user.id')
            ->from('user')
            ->leftJoin('user','addresses', null ,'addresses.user_id = user.id');

        $this->assertEquals("SELECT user.id FROM user LEFT JOIN addresses ON addresses.user_id = user.id", (string)$qb);
    }

    public function testRightJoinWithoutAlias(): void
    {
        $qb = new QueryBuilder();

        $qb->select('user.id')
            ->from('user')
            ->rightJoin('user','addresses','','addresses.user_id = user.id');

        $this->assertEquals("SELECT user.id FROM user RIGHT JOIN addresses ON addresses.user_id = user.id", (string)$qb);
    }

    public function testMultipleJoinsWithoutAlias(): void
    {
        $qb = new QueryBuilder();

        $qb->select('user.id')
            ->from('user')
            ->join('user','addresses','','addresses.user_id = user.id')
            ->innerJoin('user','car',null,'car.user_id = user.id')
            ->leftJoin('user','pet','pet','pet.user_id = user.id')
            ->rightJoin('user','child',null,'child.user_id = user.id');

        $this->assertEquals("SELECT user.id FROM user INNER JOIN addresses ON addresses.user_id = user.id INNER JOIN car ON car.user_id = user.id LEFT JOIN pet ON pet.user_id = user.id RIGHT JOIN child ON child.user_id = user.id", (string)$qb);
    }


    public function testLeftJoinWithSetExcludeAliases(): void
    {
        $qb = new QueryBuilder();

        $qb->setExcludeAliases()
            ->select('user.id')
            ->from('user')
            ->leftJoin('user','addresses','','addresses.user_id = user.id');

        $this->assertEquals("SELECT user.id FROM user LEFT JOIN addresses ON addresses.user_id = user.id", (string)$qb);
    }

    public function testMultipleJoinsWithSetExcludeAliases(): void
    {
        $qb = new QueryBuilder();

        $qb->setExcludeAliases()
            ->select('user.id')
            ->from('user')
            ->join('user','addresses','','addresses.user_id = user.id')
            ->innerJoin('user','car',null,'car.user_id = user.id')
            ->leftJoin('user','pet','pet','pet.user_id = user.id')
            ->rightJoin('user','child','','child.user_id = user.id');

        $this->assertEquals("SELECT user.id FROM user INNER JOIN addresses ON addresses.user_id = user.id INNER JOIN car ON car.user_id = user.id LEFT JOIN pet ON pet.user_id = user.id RIGHT JOIN child ON child.user_id = user.id", (string)$qb);
    }

    public function testJoinWithAliasWhenExcludingThrowsException(): void
    {
        $qb = new QueryBuilder();

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage("The given alias 'table_a' is not part of any FROM or JOIN clause table. The currently registered aliases are: a.");

        $qb->select('table_a.id')
            ->setExcludeAliases()
            ->from('table_a', 'a')
            ->join('table_a', 'table_b', 'a', 'table_b.fk_b = table_a.id')
            ->join('table_a', 'table_c', 'a', 'table_c.fk_b = table_a.id');

        self::assertEquals('', $qb->getSQL());
    }

    public function testJoinsWithExcludeAliasesButFromHasAlias(): void
    {
        $qb = new QueryBuilder();

        $qb->select('a.id')
            ->setExcludeAliases()
            ->from('table_a', 'a')
            ->join('a', 'table_b', 'a', 'table_b.fk_b = a.id')
            ->join('a', 'table_c', 'a', 'table_c.fk_b = a.id');

        self::assertEquals('SELECT a.id FROM table_a a INNER JOIN table_b ON table_b.fk_b = a.id INNER JOIN table_c ON table_c.fk_b = a.id', $qb->getSQL());
    }
}