<?php

use \Drupal\DrupalExtension\Context\DrupalContext;
use Behat\Behat\Exception\PendingException;
/*
use \Behat\Behat\Context\SnippetAcceptingContext;
use \Behat\Gherkin\Node\PyStringNode;
use \Behat\Gherkin\Node\TableNode;
*/
/**
 * Defines application features from the specific context.
 */
class FeatureContext extends DrupalContext { // implements SnippetAcceptingContext {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
  }

  /**
   * @Given /^I have a flag "([^"]*)"$/
   */
  public function iHaveAFlag($name) {
    $machine_readable = strtolower($name);
    $machine_readable = preg_replace('@[^a-z0-9_]+@','_',$machine_readable);

    $flag = flag_flag::factory_by_entity_type('node');


    // Absolutely required, will break your site if not added properties.
    $flag->name = $machine_readable;
    $flag->title = $name;

    // Properties required by the UI.
    $flag->roles = array(2); // An array of role IDs. 2 is for "authenticated users".
    $flag->types = array('article', 'page'); // An array of node types.
    $flag->flag_short = 'Flag this item';
    $flag->unflag_short = 'Unflag this item';

    // Optional properties, defaults are defined for these (and more).
    // Use a print_r() or dsm() to see all the available flag properties.
    $flag->global = FALSE;
    $flag->flag_long = '';
    $flag->flag_message = '';
    $flag->show_in_links = array(
      'teaser' => 'teaser',
      'full' => 'full',
      'rss' => 0,
      'search_index' => 0,
      'search_result' => 0,
    );
    $flag->show_on_form = TRUE;
    $flag->show_on_node = TRUE;
    $flag->show_on_teaser = TRUE;
    $flag->link_type = 'normal';

    // Save the flag.
    $flag->save();
    $flag->enable();

/*
    $configuration = array(
      'name' => $machine_readable,
      'global' => 0,
      'show_in_links' => array(
        'full' => 1,
        'teaser' => 1,
      ),
      'show_on_form' => 1,
      'title' => $name,
      'types' => array('article', 'page'),
    );
    $flag->form_input($configuration);
    $flag->save();
    $flag->enable();

    // Clear the flag cache so the new permission is seen by core.
    drupal_static_reset('flag_get_flags');

    // Grant permissions.
    $permissions = array("flag $machine_readable", "unflag $machine_readable");
    user_role_grant_permissions(DRUPAL_AUTHENTICATED_RID, $permissions);
*/
    $this->flagClearCache(array('article', 'page'), TRUE);
  }

  protected function flagClearCache($entity_types, $is_insert_or_delete = FALSE) {
    if (!is_array($entity_types)) {
      $entity_types = array($entity_types);
    }

    // Reset our flags cache, thereby making the following code aware of the
    // modifications.
    drupal_static_reset('flag_get_flags');

    if ($is_insert_or_delete) {
      // A new or deleted flag means we are changing bundles on the Flagging
      // entity, and thus need to clear the entity info cache.
      entity_info_cache_clear();
    }

    // Clear FieldAPI's field_extra cache, so our changes to pseudofields are
    // noticed. It's rather too much effort to both a) check whether the
    // pseudofield setting has changed either way, and b) specifically clear just
    // the bundles that are (or were!!) affected, so we just clear for all bundles
    // on our entity type regardlesss.
    foreach ($entity_types as $entity_type) {
      cache_clear_all("field_info:bundle_extra:$entity_type:", 'cache_field', TRUE);
    }

    if (module_exists('views')) {
      views_invalidate_cache();
    }

    // The title of a flag may appear in the menu (indirectly, via our "default
    // views"), so we need to clear the menu cache. This call also clears the
    // page cache, which is desirable too because the flag labels may have
    // changed.
    menu_rebuild();
  }

}
