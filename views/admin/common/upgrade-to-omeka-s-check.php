<h2><?php echo __('Core & Server'); ?></h2>
<?php
if (isset($prechecks['Core'])): ?>
    <p class="check-error"><?php echo __('Omeka can’t be upgraded.'); ?></p>
    <?php echo '<ul><li>' . implode('</li><li>', $prechecks['Core']) . '</li></ul>';
elseif (isset($checks['Core'])): ?>
    <p class="check-error"><?php echo __('Omeka can be upgraded, but some errors have been reported in the form.'); ?></p>
    <?php echo '<ul><li>' . implode('</li><li>', $checks['Core']) . '</li></ul>';
else: ?>
    <p><?php echo __('The precheck processor deems that the core of Omeka Classic can be upgraded on this server.'); ?></p>
    <?php if ($hasErrors == 'form'): ?>
    <p class="check-error"><?php echo __('Nevertheless, the form should be checked.'); ?></p>
    <?php endif; ?>
<?php endif; ?>
<h2><?php echo __('Plugins'); ?></h2>
<?php
// A check for the message for the plugins, except core.
$prechecksPlugins = $prechecks;
unset($prechecksPlugins['Core']);
$checksPlugins = $checks;
unset($checksPlugins['Core']);
if ($prechecksPlugins or $checksPlugins):
    $totalErrorsPlugins = count($prechecksPlugins) + count($checksPlugins); ?>
<p class="check-error"><?php echo function_exists('plural')
    ? __(plural('%d plugin can’t be upgraded.', '%d plugins can’t be upgraded.', $totalErrorsPlugins), $totalErrorsPlugins)
    :  __('%d plugins can’t be upgraded.', $totalErrorsPlugins); ?>
</p>
<p><?php echo function_exists('plural')
    ? __(plural('Fix it before upgrade.', 'Fix them before upgrade.', $totalErrorsPlugins), $totalErrorsPlugins)
    : __('Fix them before upgrade.', $totalErrorsPlugins);
?></p>
<?php else:
    echo '<p>' . __('The precheck processor deems that all active plugins with an available processor can be upgraded.') . '</p>';
endif;
?>
<table>
    <thead>
        <tr>
            <th><?php echo __('Plugin'); ?></th>
            <th><?php echo __('Installed'); ?></th>
            <th><?php echo __('Active'); ?></th>
            <th><?php echo __('Current version'); ?></th>
            <th><?php echo __('Required min version'); ?></th>
            <th><?php echo __('Required max version'); ?></th>
            <th><?php echo __('Processor'); ?></th>
            <th><?php echo __('Upgradable'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $key = 0;
        foreach ($plugins as $name => $plugin):
            $rowClass = $plugin['processor'] ? 'upgrade-has-processor' . ' ' : '';
            $rowClass .= $plugin['upgradable'] ? 'upgrade-true' : 'upgrade-false';
            $rowClass .= ' ' . (++$key % 2 ? 'odd' : 'even');
            $error = $plugin['processor'] && $plugin['installed'] && $plugin['active'] && !$plugin['upgradable'];
            $pluginProcessor = !empty($plugin['processor']) ? $plugin['processor'] : null;
            $pluginProcessorNote = $pluginProcessor ? $pluginProcessor->precheckProcessorPlugin() : null;
            $note = !empty($prechecks[$name]) || !empty($checks[$name]) || !empty($pluginProcessorNote);
        ?>
        <tr class="<?php echo $rowClass; ?>">
            <td<?php echo $error || $note ? ' rowspan="2"' : ''; ?>><?php echo $plugin['name']; ?></td>
            <td><?php echo $plugin['installed'] ? __('Yes') : __('No'); ?></td>
            <td><?php echo $plugin['active'] ? __('Yes') : __('No'); ?></td>
            <td><?php echo $plugin['version']; ?></td>
            <td><?php echo $pluginProcessor ? $pluginProcessor->minVersion : ''; ?></td>
            <td><?php echo $pluginProcessor ? $pluginProcessor->maxVersion : ''; ?></td>
            <td><?php echo $pluginProcessor && !$pluginProcessorNote ? __('Yes') : __('No'); ?></td>
            <td><?php echo $plugin['upgradable'] ? __('Yes') : __('No'); ?></td>
        </tr>
        <?php if ($error || $note): ?>
        <tr>
            <td colspan="7">
                <?php
                if (!empty($pluginProcessorNote)):
                    echo '<div>' . $pluginProcessorNote . '</div>';
                endif;
                if (!empty($prechecks[$name])):
                    echo '<div class="check-error">' . implode ('</div><div class="check-error">', $prechecks[$name]) . '</div>';
                endif;
                if (!empty($checks[$name])):
                    echo '<div class="check-error">' . implode ('</div><div class="check-error">', $checks[$name]) . '</div>';
                endif;
                ?>
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>
