<?php

/**
 * Copyright © 2015-2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Blog schema update
 */
class UpgradeSchema implements UpgradeSchemaInterface {

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $setup->startSetup();

        $version = $context->getVersion();
        $connection = $setup->getConnection();

        if (version_compare($version, '2.0.1') < 0) {

            foreach (['magefan_blog_post_relatedpost', 'magefan_blog_post_relatedproduct'] as $tableName) {
                // Get module table
                $tableName = $setup->getTable($tableName);

                // Check if the table already exists
                if ($connection->isTableExists($tableName) == true) {

                    $columns = [
                        'position' => [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'nullable' => false,
                            'comment' => 'Position',
                        ],
                    ];

                    foreach ($columns as $name => $definition) {
                        $connection->addColumn($tableName, $name, $definition);
                    }
                }
            }

            $connection->addColumn(
                    $setup->getTable('magefan_blog_post'), 'featured_img', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Thumbnail Image',
                    ]
            );
        }

        if (version_compare($version, '2.2.0') < 0) {
            /* Add author field to posts tabel */
            $connection->addColumn(
                    $setup->getTable('magefan_blog_post'), 'author_id', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Author ID',
                    ]
            );

            $connection->addIndex(
                    $setup->getTable('magefan_blog_post'), $setup->getIdxName($setup->getTable('magefan_blog_post'), ['author_id']), ['author_id']
            );
        }


        if (version_compare($version, '2.2.5') < 0) {
            /* Add layout field to posts and category tabels */
            foreach (['magefan_blog_post', 'magefan_blog_category'] as $table) {
                $table = $setup->getTable($table);
                $connection->addColumn(
                        $setup->getTable($table), 'page_layout', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Post Layout',
                        ]
                );

                $connection->addColumn(
                        $setup->getTable($table), 'layout_update_xml', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '64k',
                    'nullable' => true,
                    'comment' => 'Post Layout Update Content',
                        ]
                );

                $connection->addColumn(
                        $setup->getTable($table), 'custom_theme', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => true,
                    'comment' => 'Post Custom Theme',
                        ]
                );

                $connection->addColumn(
                        $setup->getTable($table), 'custom_layout', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Post Custom Template',
                        ]
                );

                $connection->addColumn(
                        $setup->getTable($table), 'custom_layout_update_xml', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '64k',
                    'nullable' => true,
                    'comment' => 'Post Custom Layout Update Content',
                        ]
                );

                $connection->addColumn(
                        $setup->getTable($table), 'custom_theme_from', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    'nullable' => true,
                    'comment' => 'Post Custom Theme Active From Date',
                        ]
                );

                $connection->addColumn(
                        $setup->getTable($table), 'custom_theme_to', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    'nullable' => true,
                    'comment' => 'Post Custom Theme Active To Date',
                        ]
                );
            }
        }

        if (version_compare($version, '2.3.0') < 0) {
            /* Add meta title field to posts tabel */
            $connection->addColumn(
                    $setup->getTable('magefan_blog_post'), 'meta_title', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Post Meta Title',
                'after' => 'title'
                    ]
            );

            /* Add og tags fields to post tabel */
            foreach (['type', 'img', 'description', 'title'] as $type) {
                $connection->addColumn(
                        $setup->getTable('magefan_blog_post'), 'og_' . $type, [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Post OG ' . ucfirst($type),
                    'after' => 'identifier'
                        ]
                );
            }

            /* Add meta title field to category tabel */
            $connection->addColumn(
                    $setup->getTable('magefan_blog_category'), 'meta_title', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Category Meta Title',
                'after' => 'title'
                    ]
            );

            /**
             * Create table 'magefan_blog_tag'
             */
            $table = $setup->getConnection()->newTable(
                            $setup->getTable('magefan_blog_tag')
                    )->addColumn(
                            'tag_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'Tag ID'
                    )->addColumn(
                            'title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => true], 'Tag Title'
                    )->addColumn(
                            'identifier', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null], 'Tag String Identifier'
                    )->addIndex(
                            $setup->getIdxName('magefan_blog_tag', ['identifier']), ['identifier']
                    )->setComment(
                    'Magefan Blog Tag Table'
            );
            $setup->getConnection()->createTable($table);

            /**
             * Create table 'magefan_blog_post_tag'
             */
            $table = $setup->getConnection()->newTable(
                            $setup->getTable('magefan_blog_post_tag')
                    )->addColumn(
                            'post_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'primary' => true], 'Post ID'
                    )->addColumn(
                            'tag_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'primary' => true], 'Tag ID'
                    )->addIndex(
                            $setup->getIdxName('magefan_blog_post_tag', ['tag_id']), ['tag_id']
                    )->addForeignKey(
                            $setup->getFkName('magefan_blog_post_tag', 'post_id', 'magefan_blog_post', 'post_id'), 'post_id', $setup->getTable('magefan_blog_post'), 'post_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )->addForeignKey(
                            $setup->getFkName('magefan_blog_post_tag', 'tag_id', 'magefan_blog_tag', 'tag_id'), 'tag_id', $setup->getTable('magefan_blog_tag'), 'tag_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )->setComment(
                    'Magefan Blog Post To Category Linkage Table'
            );
            $setup->getConnection()->createTable($table);
        }

        if (version_compare($version, '2.4.4') < 0) {
            $connection->addColumn(
                    $setup->getTable('magefan_blog_post'), 'media_gallery', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '2M',
                'nullable' => true,
                'comment' => 'Media Gallery',
                    ]
            );
        }

        if (version_compare($version, '2.5.2') < 0) {
            $connection->addColumn(
                    $setup->getTable('magefan_blog_post'), 'secret', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '32',
                'nullable' => true,
                'comment' => 'Post Secret',
                    ]
            );

            $connection->addColumn(
                    $setup->getTable('magefan_blog_post'), 'views_count', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                'nullable' => true,
                'comment' => 'Post Views Count',
                    ]
            );

            $connection->addColumn(
                    $setup->getTable('magefan_blog_post'), 'is_recent_posts_skip', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                'nullable' => true,
                'comment' => 'Is Post Skipped From Recent Posts',
                    ]
            );

            $connection->addIndex(
                    $setup->getTable('magefan_blog_post'), $setup->getIdxName($setup->getTable('magefan_blog_post'), ['views_count']), ['views_count']
            );

            $connection->addIndex(
                    $setup->getTable('magefan_blog_post'), $setup->getIdxName($setup->getTable('magefan_blog_post'), ['is_recent_posts_skip']), ['is_recent_posts_skip']
            );
        }

        if (version_compare($version, '2.5.3') < 0) {
            $connection->addColumn(
                    $setup->getTable('magefan_blog_post'), 'short_content', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '2M',
                'nullable' => true,
                'comment' => 'Post Short Content',
                    ]
            );
            $connection->addColumn(
                    $setup->getTable('magefan_blog_post'), 'directions_content', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '2M',
                'nullable' => true,
                'comment' => 'Post Directions Content',
                    ]
            );
            $connection->addColumn(
                    $setup->getTable('magefan_blog_post'), 'ingredients_content', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '2M',
                'nullable' => true,
                'comment' => 'Post Ingredients Content',
                    ]
            );

            $connection->addColumn(
                    $setup->getTable('magefan_blog_post'), 'ingredients_pdp', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '2M',
                'nullable' => true,
                'comment' => 'Post Ingredients Content',
                    ]
            );

            $connection->addColumn(
                    $setup->getTable('magefan_blog_post'), 'ingredients_pdp_other', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '2M',
                'nullable' => true,
                'comment' => 'Post Ingredients Content',
                    ]
            );

            $connection->addColumn(
                    $setup->getTable('magefan_blog_post'), 'suggestions_content', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '2M',
                'nullable' => true,
                'comment' => 'Post Suggestions Content',
                    ]
            );
        }


        if (version_compare($version, '2.6.0') < 0) {
            /**
             * Create table 'magefan_blog_comment'
             */
            $table = $setup->getConnection()->newTable(
                            $setup->getTable('magefan_blog_comment')
                    )->addColumn(
                            'comment_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'Comment ID'
                    )->addColumn(
                            'parent_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false], 'Parent Comment ID'
                    )->addColumn(
                            'post_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false], 'Post ID'
                    )->addColumn(
                            'customer_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => true], 'Customer ID'
                    )->addColumn(
                            'admin_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => true], 'Admin User ID'
                    )->addColumn(
                            'status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['nullable' => false], 'Comment status'
                    )->addColumn(
                            'author_type', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['nullable' => false], 'Author Type'
                    )->addColumn(
                            'author_nickname', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false], 'Comment Author Nickname'
                    )->addColumn(
                            'author_email', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => true], 'Comment Author Email'
                    )->addColumn(
                            'text', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', [], 'Text'
                    )->addColumn(
                            'creation_time', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'Comment Creation Time'
                    )->addColumn(
                            'update_time', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'Comment Update Time'
                    )->addIndex(
                            $installer->getIdxName('magefan_blog_comment', ['parent_id']), ['parent_id']
                    )->addIndex(
                            $installer->getIdxName('magefan_blog_comment', ['post_id']), ['post_id']
                    )->addIndex(
                            $installer->getIdxName('magefan_blog_comment', ['customer_id']), ['customer_id']
                    )->addIndex(
                            $installer->getIdxName('magefan_blog_comment', ['admin_id']), ['admin_id']
                    )->addIndex(
                            $installer->getIdxName('magefan_blog_comment', ['status']), ['status']
                    )->addForeignKey(
                    $installer->getFkName('magefan_blog_comment', 'post_id', 'magefan_blog_post', 'post_id'), 'post_id', $installer->getTable('magefan_blog_post'), 'post_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }

}
