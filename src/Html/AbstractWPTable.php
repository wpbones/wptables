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
  protected $singular;

  protected $plural;

  protected $itemPerPageName;

  protected $defaultItemPerPage = 5;

  protected $columns = [];

  protected $screenOptionsLabel = '';

  protected $searchBoxButtonLabel = '';

  protected $searchBoxInputId = '';

  protected $addNewLabel = '';

  /**
   * The name of this list table.
   *
   * @var string
   */
  protected $name = '';

  /**
   * Display a well format title above the list table.
   *
   * @var string
   */
  protected $title = '';

  /**
   * Set TRUE to enable the checkboxes column.
   *
   * @var bool
   */
  protected $checkBoxes = true;

  /**
   * Set TRUE to enable Ajax pagination.
   *
   * @var bool
   */
  protected $ajax = false;

  /**
   * Set to TRUE to display the search box in the top right of the list table.
   * When true, you may override `getSearchBoxButtonLabelAttribute()` and
   * `getSearchBoxInputIdAttribute()` attribute methods. Also, you can use the
   * related properties: `searchBoxButtonLabel` and `searchBoxInputId`.
   *
   * @var bool
   */
  protected $searchBox = false;

  /**
   * List of the input type hidden inject into the form.
   *
   * @var array
   */
  protected $inputsHidden = [
    'page',
    'post_type',
    'orderby',
    'order',
    '_action_result',
  ];

  /**
   * You can set these properties after an action.
   *
   * @var string
   */
  protected $successMessage = '';

  protected $errorMessage = '';

  protected $infoMessage = '';

  protected $warningMessage = '';

  /**
   * AbstractWPTable constructor.
   *
   * You can use the `boot()` method instead override the constructor.
   *
   * @param array $args Optional. The standar WP List Table args.
   */
  public function __construct( $args = [] )
  {
    if ( method_exists( $this, 'boot' ) ) {
      $this->boot();
    }

    $args = wp_parse_args( $args,
                           [
                             'plural'   => $this->getPluralAttribute(),
                             'singular' => $this->getSingularAttribute(),
                             'ajax'     => $this->ajax,
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

  /**
   * Return an instance of class. This is a useful method.
   * Anyway, any method or miss method will return an instance of class.
   *
   * @param array $args
   *
   * @return static
   */
  public static function init( $args = [] )
  {
    return new static( $args );
  }

  /**
   * Used to set attribute by a static call.
   * Anyway, if the static method is not found, an instance of class is returned.
   *
   * @param $name
   * @param $arguments
   *
   * @return static
   */
  public static function __callStatic( $name, $arguments )
  {
    $instance = new static();

    $method = "set" . Str::studly( $name ) . 'Attribute';

    if ( method_exists( $instance, $method ) ) {
      if ( empty( $arguments ) ) {
        $instance->{$method}();
      }
      else {
        $instance->{$method}( $arguments[ 0 ] );
      }
    }

    return $instance;
  }

  public function __call( $name, $arguments )
  {
    $method = "set" . Str::studly( $name ) . 'Attribute';

    if ( method_exists( $this, $method ) ) {
      if ( empty( $arguments ) ) {
        $this->{$method}();
      }
      else {
        $this->{$method}( $arguments[ 0 ] );
      }
    }

    return $this;
  }

  public function __toString()
  {
    return $this->html();
  }

  /*
  |--------------------------------------------------------------------------
  | Get/Set attributes
  |--------------------------------------------------------------------------
  |
  | Here you'll find the get/set for dynamic attributes. These can be used
  | either for override a subclass or like fluent instance.
  |
  */

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
    return $this->items;
  }

  public function setItems( $value )
  {
    $this->items = $value;

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

  public function getSearchBoxButtonLabelAttribute()
  {
    return empty( $this->searchBoxButtonLabel ) ? __( 'Search' ) : $this->searchBoxButtonLabel;
  }

  public function setSearchBoxButtonLabelAttribute( $value )
  {
    $this->searchBoxButtonLabel = $value;

    return $this;
  }

  public function getSearchBoxInputIdAttribute()
  {
    return empty( $this->searchBoxInputId ) ? 'search_id' : $this->searchBoxInputId;
  }

  public function setSearchBoxInputIdAttribute( $value )
  {
    $this->searchBoxInputId = $value;

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

  public function getAddNewLabelAttribute()
  {
    return empty( $this->addNewLabel ) ? __( 'Add New' ) : $this->addNewLabel;
  }

  public function getTitleAttribute()
  {
    return $this->title;
  }

  public function setTitleAttribute( $value )
  {
    $this->title = $value;

    return $this;
  }

  public function getAddNewUri()
  {
    return false;
  }

  /**
   * You have override this method in order to display the table views.
   * This will return an associative array look like:
   *
   *    return array(
   *      'all'     => __( 'All' ),
   *      'publish' => __( 'Publish' ),
   *    );
   *
   * @return array
   */
  public function getViews()
  {
    return [];
  }

  /**
   * Usually, you have to override this method when you are provide some view.
   * This will return the current view. For example, you may to check the $_REQUEST[] global var.
   *
   * @return string
   */
  public function getCurrentView()
  {
    return 'all';
  }

  /**
   * You have to override this method to provide the bulk actions drop down menu.
   * Will return an associative array look like:
   *
   *    return [
   *      'delete' => __( 'Delete' )
   *    ];
   *
   * Also, you can use $view input parameter to figure out which view is displayed.
   *
   * @param string $view The current view.
   *
   * @return array
   */
  public function getBulkActionsForView( $view )
  {
    return [];
  }

  /**
   * Return the search value of input;
   *
   * @return bool|string
   */
  public function getSearchValue()
  {
    if ( $this->searchBox ) {
      if ( isset( $_REQUEST[ 's' ] ) && ! empty( $_REQUEST[ 's' ] ) ) {
        return $_REQUEST[ 's' ];
      }
    }

    return false;
  }

  /**
   * Will call a method when find a bulk action.
   * For example, if the action is "keep_on_trash" then you will have `processBulkActionKeepOnTrash()` method.
   *
   * @return bool
   */
  protected function processBulkActions()
  {
    $action = $this->getCurrentBulkAction();

    if ( $action ) {

      $method = 'processBulkAction' . Str::studly( $action );

      if ( method_exists( $this, $method ) ) {

        $items = isset( $_REQUEST[ $this->getIdAttribute() ] ) ? $_REQUEST[ $this->getIdAttribute() ] : [];

        $this->{$method}( $items );

        return true;
      }
    }

    return false;
  }

  /**
   * Return the current bulk action. If no bulk actions are set will return FALSE.
   *
   * @return bool
   */
  protected function getCurrentBulkAction()
  {
    if ( isset( $_REQUEST[ 'action' ] ) && -1 != $_REQUEST[ 'action' ] ) {
      return $_REQUEST[ 'action' ];
    }

    if ( isset( $_REQUEST[ 'action2' ] ) && -1 != $_REQUEST[ 'action2' ] ) {
      return $_REQUEST[ 'action2' ];
    }

    return false;
  }

  /**
   * Usually, you shouldn't override this method.
   * Instead use `getViewCount{ViewKey}`.
   *
   * This will return the number of items for selected view.
   *
   * @param string $view The view key.
   *
   * @return int
   */
  protected function getCountForView( $view )
  {
    if ( $view === 'all' ) {
      return count( $this->items );
    }

    $method = 'getViewCount' . Str::studly( $view );

    if ( method_exists( $this, $method ) ) {
      return $this->{$method}();
    }

    return 0;
  }

  /**
   * Usually, you shouldn't override this method.
   * Instead use `getViewQueryArg{ViewKey}`.
   *
   * This will return an empty array for `all` key or your query args like an associative
   * array, look like:
   *
   *  [
   *    'ingredients' => 'cream'
   *  ]
   *
   * @param string $view The view key.
   *
   * @return array
   */
  protected function getQueryArgsForView( $view )
  {
    if ( $view === 'all' ) {
      return [];
    }

    $method = 'getViewQueryArg' . Str::studly( $view );

    if ( method_exists( $this, $method ) ) {
      return $this->{$method}();
    }

    return [];

  }

  /*
  |--------------------------------------------------------------------------
  | Original WP List Table
  |--------------------------------------------------------------------------
  |
  | Here you'll find the subclassed method from WP List Table
  |
  */

  public function get_bulk_actions()
  {
    $currentView = $this->getCurrentView();

    return $this->getBulkActionsForView( $currentView );
  }

  public function get_views()
  {
    $views = [];

    $currentView = $this->getCurrentView();

    $allViews = $this->getViews();

    if ( ! empty( $allViews ) ) {

      if ( ! in_array( 'all', array_keys( $allViews ) ) ) {
        $allViews = array_merge( [ 'all' => __( 'All' ) ], $allViews );
      }

      foreach ( $allViews as $key => $label ) {

        $count = $this->getCountForView( $key );

        if ( ! empty( $count ) ) {
          $class = ( $key == $currentView ) ? 'current' : '';
          $href  = $this->getUriForView( $key );

          $views[ $key ] = sprintf( '<a class="%s" href="%s">%s <span class="count">(%s)</span></a>',
                                    $class,
                                    $href,
                                    $label,
                                    $count
          );
        }
      }

    }

    return $views;
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
   * Will return TRUE if the items are prepared. Instead, will return FALSE when any bulk actions have been performed.
   */
  public function prepare_items()
  {
    $this->_column_headers = $this->get_column_info();

    // Process bulk actions
    $this->processBulkActions();

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

    return true;

  }

  /**
   * Message to be displayed when there are no items
   *
   */
  public function no_items()
  {
    // Default message
    echo '<p style="text-align:center">';
    echo '<strong>';
    printf( __( 'No %s found.' ), $this->name );
    echo '</strong>';


    if ( $this->getSearchValue() ) {

      echo '<br/>';

      _e( 'Please, check again your search parameters.' );
    }

    echo '</p>';
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

  public function html()
  {
    ob_start();

    if ( ! empty( $this->title ) ) {
      $this->displayTitle();
    }

    $this->prepare_items();

    $this->views();

    ?>
    <form id="<?php echo $this->getIdAttribute() ?>"
          class="<?php echo $this->getNameAttribute() ?>"
          method="get"><?php

    if ( $this->searchBox ) {
      $this->search_box( $this->getSearchBoxButtonLabelAttribute(), $this->getSearchBoxInputIdAttribute() );
    }

    foreach ( $this->inputsHidden as $name ) {
      if ( isset( $_REQUEST[ $name ] ) ) {
        $value = is_array( $_REQUEST[ $name ] ) ? esc_attr( $_REQUEST[ $name ][ 0 ] ) : esc_attr( $_REQUEST[ $name ] );
        echo "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\" />";
      }
    }

    parent::display();

    ?></form><?php

    if ( ! empty( $this->successMessage ) ) {
      ?>
      <div class="notice notice-success is-dismissible">
        <p><?php echo $this->successMessage ?></p>
      </div>
      <?php
    }

    if ( ! empty( $this->errorMessage ) ) {
      ?>
      <div class="notice notice-error is-dismissible">
        <p><?php echo $this->errorMessage ?></p>
      </div>
      <?php
    }

    if ( ! empty( $this->warningMessage ) ) {
      ?>
      <div class="notice notice-warning is-dismissible">
        <p><?php echo $this->warningMessage ?></p>
      </div>
      <?php
    }

    if ( ! empty( $this->infoMessage ) ) {
      ?>
      <div class="notice notice-info is-dismissible">
        <p><?php echo $this->infoMessage ?></p>
      </div>
      <?php
    }

    $content = ob_get_contents();
    ob_end_clean();

    return $content;
  }

  public function display()
  {
    echo $this->html();
  }

  /*
  |--------------------------------------------------------------------------
  | Private methods
  |--------------------------------------------------------------------------
  |
  | Here you'll find the private methods.
  |
  */

  private function displayTitle()
  {
    ?>
    <h1 class="wp-heading-inline">
      <?php echo $this->title ?>
    </h1>

    <?php if ( ! empty( $this->getAddNewUri() ) && ! empty( $this->getAddNewLabelAttribute() ) ) : ?>

    <a href="<?php echo esc_url( $this->getAddNewUri() ); ?>"
       class="page-title-action">
      <?php echo $this->getAddNewLabelAttribute() ?>
    </a>

  <?php endif; ?>

    <hr class="wp-header-end">

    <h2 class="screen-reader-text">
      <?php echo $this->title ?>
    </h2>
    <?php
  }

  private function isAjax()
  {
    if ( defined( 'DOING_AJAX' ) ) {
      return true;
    }
    if ( isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest'
    ) {
      return true;
    }
    else {
      return false;
    }
  }

  private function getReferer()
  {
    $referer = $this->isAjax() ? $_SERVER[ 'HTTP_REFERER' ] : $_SERVER[ 'REQUEST_URI' ];

    $remove = [
      '_wp_http_referer',
      'action',
      'action2',
      '_wpnonce',
      $this->_args[ 'singular' ],
      $this->_args[ 'plural' ],
      'paged',
      '_action_result',
    ];

    foreach ( array_keys( $this->getViews() ) as $view ) {
      $remove = array_filter( array_merge( $remove, array_keys( $this->getQueryArgsForView( $view ) ) ) );
    }

    $referer = remove_query_arg( $remove, $referer );

    return $referer;

  }

  private function getUriForView( $view )
  {
    $args = $this->getQueryArgsForView( $view );

    if ( $args ) {
      return add_query_arg( $this->getQueryArgsForView( $view ), $this->getReferer() );
    }

    return $this->getReferer();
  }


}

