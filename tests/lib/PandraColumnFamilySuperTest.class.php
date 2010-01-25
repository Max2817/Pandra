<?php
require_once 'PHPUnit/Framework.php';
require_once(dirname(__FILE__).'/../../config.php');
require_once dirname(__FILE__).'/../../lib/ColumnFamilySuper.class.php';

// SuperColumn wrapper
class TestSuperColumn extends PandraSuperColumn {
    public function init() {
        $this->addColumn('title', 'string');
        $this->addColumn('content');
        $this->addColumn('author', 'string');
    }
}

// ColumnFamily (SuperColumn Wrapper)
class TestCFSuper extends PandraColumnFamilySuper {

    public function init() {
        $this->setKeySpace('Keyspace1');
        $this->setName('Super1');

        $this->addSuper(new TestSuperColumn('blog-slug-1'));
        $this->addSuper(new TestSuperColumn('blog-slug-2'));
    }
}

/**
 * Test class for PandraColumnFamilySuper.
 * Generated by PHPUnit on 2010-01-09 at 11:52:22.
 */
class PandraColumnFamilySuperTest extends PHPUnit_Framework_TestCase {
    /**
     * @var    PandraColumnFamilySuper
     * @access protected
     */
    protected $obj;

    private $_keyID = 'PandraCFTest';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
        $this->obj = new TestCFSuper();
        $this->obj->keyID = $this->_keyID;
        Pandra::connect('default', 'localhost');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown() {
        Pandra::disconnectAll();
    }

    public function testAddColumn() {
        $newSuperName = 'newGenericSuper';
        $this->assertTrue($this->obj->addColumn($newSuperName) instanceof PandraSuperColumn);
        $this->assertTrue($this->obj->getColumn($newSuperName)->getName() == $newSuperName);
    }

    public function testAddSuper() {
        $newSuperName = 'newTestSuperColumn';
        $this->assertTrue($this->obj->addSuper(new TestSuperColumn($newSuperName)) instanceof PandraSuperColumn);
        $this->assertTrue($this->obj->getColumn($newSuperName)->getName() == $newSuperName);
    }

    public function testGetSuper() {
        $this->assertTrue($this->obj->getSuper('blog-slug-1') instanceof PandraSuperColumn);
    }

    public function testIsModified() {
        $this->assertTrue($this->obj->reset());
        
        $this->obj['blog-slug-1']['title'] = 'NEW TITLE';
        $this->assertTrue($this->obj->isModified());
    }

    public function testIsDeleted() {
        $this->obj->delete();
        $this->assertTrue($this->obj->getDelete());
    }

    public function testSaveLoadDelete() {

return;
        // Save it       
        $this->obj['blog-slug-1']['title'] = 'My First Blog';
        $this->obj['blog-slug-1']['content'] = 'Can I be in the blog-o-club too?';

        $this->obj['blog-slug-2']['title'] = 'My Second Blog, and maybe the last';
        $this->obj['blog-slug-2']['content'] = 'I promise to write something soon!';
        
        $this->assertTrue($this->obj->save());

        // Grab some konown values to test with
        $colTitleValue = $this->obj['blog-slug-1']['title'];
        $colTitleValue2 = $this->obj['blog-slug-2']['title'];

        // Re-Load, check saved data
        $this->obj = NULL;
        $this->obj = new TestCFSuper($this->_keyID);

        // Test at least 2 supercolumns to make sure population is ok
        $this->assertTrue($colTitleValue == $this->obj['blog-slug-1']['title']);
        $this->assertTrue($colTitleValue2 == $this->obj['blog-slug-2']['title']);

        // Delete columnfamily
        $this->obj->delete();

        $this->obj->save();

    }
}
?>