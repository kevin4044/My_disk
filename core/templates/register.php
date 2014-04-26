<?php
/**
 * Created by PhpStorm.
 * User: wangjunlong
 * Date: 14-4-26
 * Time: 下午4:25
 */
?>
<form action="index.php" method="post">
    <fieldset>
        <?php if (!empty($_['redirect'])) {
            echo '<input type="hidden" name="redirect_url" value="' . $_['redirect'] . '" />';
        } ?>
        <p class="infield">
            <label for="user" class="infield"><?php echo $l->t('Username'); ?></label>
            <input type="text" name="user" id="user"
                   value="<?php echo !empty($_POST['user']) ? $_POST['user'] . '"' : '" autofocus'; ?> autocomplete="
                   off" required />
        </p>

        <p class="infield">
            <label for="password" class="infield"><?php echo $l->t('Password'); ?></label>
            <input type="password" name="password" id="password" value=""
                   required <?php echo !empty($_POST['user']) ? 'autofocus' : ''; ?> />
        </p>
        <input type="submit" id="submit" class="login" value="<?php echo $l->t('Register'); ?>"/>
    </fieldset>
</form>
