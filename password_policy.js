(function ($) {
/**
 * Overriding the standard password strength check
 */
Drupal.behaviors.passwordOverride = {
  attach: function (context, settings) {
    // Set a default for our pw_status.
    pw_status = {
      strength: 0,
      message: '',
      indicatorText: '',
    }

    // We take over the keyup function on password and instead make a call to 
    // the server to evaluate the password. When we get the status back we
    // update it.  Then we call focus to all the normal drupal password update.
    $('input.password-field', context).once('passworda', function () {
      passwordInput = $(this);
      passwordCheck = function (e) {
        e.stopImmediatePropagation();
        $.getJSON(
          "/password_policy/check?password=" + encodeURIComponent(passwordInput.val()),
          function(data) {
            pw_status = data;
            passwordInput.trigger('focus');
          }
        );
      };
      passwordInput.keyup(passwordCheck);
    });
    // We are overriding the normal evaluatePasswordStrength and instead are
    // just returning the current status.
    Drupal.evaluatePasswordStrength = function (password, translate) {
      return pw_status;
    };
  },
};
})(jQuery);
