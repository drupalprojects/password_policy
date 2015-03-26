<?php
/**
 * @file
 * Contains PasswordPolicyCondition.
 */

/**
 * Class PasswordPolicyCondition
 *
 * This class is used for conditionally executing policies.
 */
class PasswordPolicyCondition extends PasswordPolicyItem {
  protected $ppType = array('item', 'condition');

  public function match($account) {
    if ($func = $this->func('condition callback')) {
      return $func($account, $this);
    }
  }
}
