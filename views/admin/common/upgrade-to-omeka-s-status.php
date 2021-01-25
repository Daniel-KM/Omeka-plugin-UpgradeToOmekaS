<?php
/**
 * @var Omeka_View $this
 * @var array $previousParams
 * @var bool $hasPreviousUpgrade
 * @var bool $isStopped
 * @var bool $isCompleted
 * @var bool $isError
 * @var bool $isReset
 */

?>
<?php if ($hasPreviousUpgrade): ?>
<h2><?php echo __('Previous Upgrade'); ?></h2>

    <?php if ($isStopped): ?>
    <h3><?php echo __('Stopped'); ?></h3>
    <p><?php echo __('The previous upgrade was stopped.'); ?></p>
    <?php endif; ?>

    <?php if ($isCompleted): ?>
    <h3><?php echo __('Completed!'); ?></h3>
    <p><?php echo __('The previous upgrade finished successfully!'); ?></p>
    <p><?php echo __('Go to your %snew site%s built on Omeka Semantic and %slogin%s to see the new world.',
        '<a href="' . $previousParams['url'] . '" target="_blank">', '</a>',
        '<a href="' . $previousParams['url'] . '/login" target="_blank">', '</a>'); ?> </p>
    <p class="explanation note"><?php
        echo __('Note') . ': ' . __('The url may be wrong if the config of the server is hardly customized.');
        echo ' ';
        echo __('If the public page doesn’t work, reset the theme to the default in the Omeka S admin board.');
        echo ' ';
        echo '<br/>';
        echo '<strong>';
        echo __('IMPORTANT');
        echo '</strong> ';
        echo __('If there are character encoding issues, see %sreadme%s to fix them.',
            '<a href="https://github.com/Daniel-KM/Omeka-plugin-UpgradeToOmekaS#database-encoding-fix" target="_blank">', '</a>');
    ?></p>
    <?php endif; ?>

    <?php if ($isError): ?>
    <h3><?php echo __('Error!'); ?></h3>
    <p><?php
        echo __('An error occurred during the previous upgrade.');
        echo ' ' . __('Check the logs and clean your install if needed.');
    ?></p>
    <p><?php echo __('Your current install is never modified.'); ?></p>
    <?php endif; ?>

    <p><?php echo __('If you want to keep Omeka S, you can remove the parameters used to upgrade (only logs are kept).'); ?></p>
    <a class="medium blue button" href="<?php echo url('/upgrade-to-omeka-s/index/clear'); ?>">
        <?php echo __('Clear Upgrade Parameters'); ?>
    </a>

    <?php if ($isStopped || $isCompleted || $isError): ?>
    <p><?php echo __('You may want to reset the main status of the upgrade to retry it with different parameters.'); ?></p>
    <p><?php echo __('To reset the process is required if you want to remove automatically the created tables and the copied files.'); ?></p>
    <a class="medium blue button" href="<?php echo url('/upgrade-to-omeka-s/index/reset'); ?>"><?php echo __('Reset Status'); ?></a>
    <?php endif; ?>

    <?php if ($isReset && !empty($previousParams['database'])): ?>
    <p><?php echo __('You can safely remove automatically the created tables (%s), the Omeka S folder (%s) and the copied files of the previous process, if wished.',
        $previousParams['database']['type'] == 'share'
            ? __('shared database')
            : $previousParams['database']['host'] . (empty($previousParams['database']['port']) ? '' : ':' . $previousParams['database']['port']) . ' / ' . $previousParams['database']['dbname'],
        $previousParams['base_dir']); ?></p>
    <p><?php echo __('The database itself won’t be removed.'); ?></p>
    <a class="medium red button" href="<?php echo url('/upgrade-to-omeka-s/index/remove'); ?>" onclick="return confirm('<?php echo __('Are you sure to remove all tables and files of Omeka S?'); ?>');">
        <?php echo __('Remove Tables and Files of Omeka Semantic'); ?>
    </a>
    <?php endif; ?>
<?php endif; ?>
