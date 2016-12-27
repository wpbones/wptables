<?php

namespace WPKirk\WPTables\Html;

use WPKirk\WPBones\Support\Str;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

abstract class AbstractWPTable extends \WP_List_Table
{
  protected $_items = [];

  protected $singular;

  protected $plural;

  protected $name;

  protected $ajax = false;

  protected $itemPerPageName;

  protected $defaultItemPerPage = 5;

  protected $checkBoxes = true;

  protected $columns = [];

  protected $screenOptionsLabel = '';

  public function __construct( $args = [] )
  {
    $args = wp_parse_args( $args,
                           [
                             'plural'   => $this->getPluralAttribute(),
                             'singular' => $this->getSingularAttribute(),
                             'ajax'     => $this->ajax,
                             'screen'   => $this->getIdAttribute(),
                           ]
    );

    parent::__construct( $args );
  }

  public function __get( $name )
  {
    $null = parent::__get( $name );

    if ( is_null( $null ) ) {

      $method = "get" . Str::studly( $name ) . 'Attribute';

      if ( method_exists( $this, $method ) ) {
        return $this->{$method}();
      }
    }
  }

  public function __set( $name, $value )
  {
    $null = parent::__set( $name, $value ); // TODO: Change the autogenerated stub

    if ( is_null( $null ) ) {

      $method = "set" . Str::studly( $name ) . 'Attribute';

      if ( method_exists( $this, $method ) ) {
        return $this->{$method}( $value );
      }
    }
  }

  public static function init( $args = [] )
  {
    return new static( $args );
  }

  public static function __callStatic( $name, $arguments )
  {
    $instance = new static();

    $method = "set" . Str::studly( $name ) . 'Attribute';

    if ( method_exists( $instance, $method ) ) {
      $instance->{$method}( $arguments[ 0 ] );
    }

    return $instance;
  }

  public function __call( $name, $arguments )
  {
    $method = "set" . Str::studly( $name ) . 'Attribute';

    if ( method_exists( $this, $method ) ) {
      $this->{$method}( $arguments[ 0 ] );
    }

    return $this;
  }

  public function setRegisterScreenOptionAttribute()
  {
    $GLOBALS[ $this->getIdAttribute() ] = $this;

    $this->addScreenOption();

    return $this;
  }

  public function addScreenOption()
  {
    $args = [
      'label'   => $this->getScreenOptionLabelAttribute(),
      'default' => $this->getDefaultItemPerPageAttribute(),
      'option'  => $this->getItemPerPageNameAttribute(),
    ];

    add_screen_option( 'per_page', $args );
  }

  public function getNameAttribute()
  {
    return empty( $this->name ) ? get_called_class() : $this->name;
  }

  public function setNameAttribute( $value )
  {
    $this->name = $value;

    return $this;
  }

  public function getSingularAttribute()
  {
    return $this->singular;
  }

  public function setSingularAttribute( $value )
  {
    $this->singular            = sanitize_key( $value );
    $this->_args[ 'singular' ] = $this->singular;

    return $this;
  }

  public function getPluralAttribute()
  {
    return $this->plural;
  }

  public function setPluralAttribute( $value )
  {
    $this->plural            = sanitize_key( $value );
    $this->_args[ 'plural' ] = $this->plural;

    return $this;
  }

  public function getItems( $args = [] )
  {
    return $this->_items;
  }

  public function setItems( $value )
  {
    $this->_items = $value;

    return $this;
  }

  public function getIdAttribute()
  {
    return sanitize_title( $this->getNameAttribute() );
  }

  public function getDefaultItemPerPageAttribute()
  {
    return $this->defaultItemPerPage;
  }

  public function setDefaultItemPerPageAttribute( $value )
  {
    $this->defaultItemPerPage = $value;

    return $this;
  }

  public function getItemPerPageNameAttribute()
  {
    $this->itemPerPageName = $this->getIdAttribute() . '_per_page';

    return $this->itemPerPageName;
  }

  public function getScreenOptionLabelAttribute()
  {
    return empty( $this->screenOptionsLabel ) ? __( 'Items number' ) : $this->screenOptionsLabel;
  }

  public function setScreenOptionLabelAttribute( $value )
  {
    $this->screenOptionsLabel = $value;

    return $this;
  }

  /**
   * Associative array with the list of columns.
   *
   * @return array
   */
  public function getColumnsAttribute()
  {
    return $this->columns;
  }

  public function setColumnsAttribute( $value )
  {
    $this->columns = $value;

    return $this;
  }

  public function get_columns()
  {
    $columns = [];

    if ( $this->checkBoxes ) {
      $columns = [
        'cb' => '<input type="checkbox" />',
      ];
    }

    $columns = array_merge( $columns, $this->getColumnsAttribute() );

    return $columns;
  }

  /**
   * Associative array with the list of sortable columns.
   *
   * @return array
   */
  public function getSortableColumnsAttribute()
  {
    return [];
  }

  public function get_sortable_columns()
  {
    return $this->getSortableColumnsAttribute();
  }

  /**
   * Render the bulk edit checkbox
   *
   * @param array $item
   *
   * @return string
   */
  public function column_cb( $item )
  {
    if ( $this->checkBoxes ) {

      if ( method_exists( $this, 'getCheckBoxValueAttribute' ) ) {
        $value = $this->getCheckBoxValueAttribute( $item );
      }
      else if ( method_exists( $this, 'getCheckBoxColumnNameAttribute' ) ) {
        $value = $item[ $this->getCheckBoxColumnNameAttribute() ];
      }
      else {
        $value = $item[ $this->get_primary_column() ];
      }

      return sprintf(
        '<input type="checkbox" name="%s[]" value="%s" />',
        $this->getIdAttribute(),
        $value
      );
    }
  }

  /**
   * Handles data query and filter, sorting, and pagination.
   */
  public function prepare_items()
  {
    $this->_column_headers = $this->get_column_info();

    // Process bulk action
    if ( method_exists( $this, 'processBulkAction' ) ) {
      call_user_func( [ $this, 'processBulkAction' ] );
    }

    $perPage = $this->get_items_per_page( $this->getItemPerPageNameAttribute(), $this->getDefaultItemPerPageAttribute() );

    /**
     * REQUIRED for pagination. Let's figure out what page the user is currently
     * looking at. We'll need this later, so you should always include it in
     * your own package classes.
     */
    $currentPage = $this->get_pagenum();

    // get items
    $items = $this->getItems();

    /**
     * REQUIRED for pagination. Let's check how many items are in our data array.
     * In real-world use, this would be the total number of items in your database,
     * without filtering. We'll need this later, so you should always include it
     * in your own package classes.
     */
    $totalItems = count( $items );

    /**
     * The WP_List_Table class does not handle pagination for us, so we need
     * to ensure that the data is trimmed to only the current page. We can use
     * array_slice() to
     */
    $sliceItems = array_slice( $items, ( ( $currentPage - 1 ) * $perPage ), $perPage );

    /**
     * REQUIRED. Now we can add our *sorted* data to the items property, where
     * it can be used by the rest of the class.
     */

    $this->items = $sliceItems;

    /**
     * REQUIRED. We also have to register our pagination options & calculations.
     */
    $this->set_pagination_args(
      [
        'total_items' => $totalItems,
        'per_page'    => $perPage,
        'total_pages' => ceil( $totalItems / $perPage ),
      ]
    );

  }

  /**
   * Message to be displayed when there are no items
   *
   * @since  3.1.0
   * @access public
   */
  public function no_items()
  {
    if ( method_exists( $this, 'getNoItemsMessage' ) ) {
      call_user_func( [ $this, 'getNoItemsMessage' ] );
    }
    else {
      parent::no_items();

    }

  }

  /**
   * Display a cel content for a column.
   *
   * @param array  $item       The single item
   * @param string $columnName Column name
   *
   * @return mixed
   */
  public function column_default( $item, $columnName )
  {
    return $item[ $columnName ];
  }

  public function display()
  {
    $this->prepare_items();
    parent::display();
  }


}
