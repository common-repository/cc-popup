<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<option value="<?= $value; ?>"<?php if ( ! empty ( $selected ) ) : ?> <?= $selected; ?><?php endif; ?>><?= $option; ?></option>