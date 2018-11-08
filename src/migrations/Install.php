<?php
/**
 * Splashing Imagesfor Craft CMS 3.x
 * @link      https://studioespresso.co
 * @copyright Copyright (c) 2018 Studio Espresso
 */

namespace studioespresso\splashingimages\migrations;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use studioespresso\splashingimages\records\UserRecord;

/**
 * Splashing Images Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    Studio Espresso
 * @package   Splashing Images
 * @since     2.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */


    protected function createTables()
    {
        $tablesCreated = false;

        // splashing_images_users table
        $tableSchema = Craft::$app->db->schema->getTableSchema(UserRecord::tableName());
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                UserRecord::tableName(),
                [
                    'id' => $this->primaryKey(),
                    'user' => $this->integer(),
                    'token' => $this->tinyText(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),

                ]
            );
        }

        return $tablesCreated;
    }


    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {

        // $name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%splashing_images_users}}', 'user'),
            '{{%splashing_images_users}}',
            'user',
            '{{%users}}',
            'id',
            'CASCADE'
        );

    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists(UserRecord::tableName());
    }
}
