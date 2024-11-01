<?php if ( 'responsive' == $embedType ) : ?>
    <div>
        <div style="padding-top:<?= $aspectRatio; ?>%; width: 100%; position: relative">
            <div id="elementId_<?= $uniqID; ?>" style="width: 100%; height: 100%; position: absolute; top: 0"></div>
        </div>
    </div>
<?php else : ?>
    <div id="elementId_<?= $uniqID; ?>" style="width: <?= $width; ?>px; height: <?= $height; ?>px;"></div>
<?php endif; ?>

<script>

<?php if($scalemode == 'manual') { ?>

    var options = {
		<?= $tracking_context ? '"contextId": "' . $tracking_context . '",' : ''; ?>
        "clientId": "<?= $clientId; ?>",
        "xcontentId": "<?= $contentID; ?>",
        "sessId": "<?= $sessId; ?>",
        "embedCodeId": "<?= $embedCodeId; ?>",
        "rtie": {
            "cropmode": "pixel",
            "quality":"<?=$quality?>",
            "scalemode":"<?=$scalemode?>",
            <?php if($cropx != ''){?>
            "cropx" : <?=$cropx?>,
            "cropy" : <?=$cropy?>,
            "croph" : <?=$croph?>,
            "cropw" : <?=$cropw?>,
            <?php } ?>            
            "enhance":"brightness:<?=($brightness != '' ? $brightness : 100); ?>,contrast:<?=($contrast != '' ? $contrast : 100); ?>,sharpness:<?=($sharpness != '' ? $sharpness : 100); ?>,color:<?=($color != '' ? $color : 100); ?>"
        }
    };
    <?php } else { ?>

        var options = {
		<?= $tracking_context ? '"contextId": "' . $tracking_context . '",' : ''; ?>
        "clientId": "<?= $clientId; ?>",
        "xcontentId": "<?= $contentID; ?>",
        "sessId": "<?= $sessId; ?>",
        "embedCodeId": "<?= $embedCodeId; ?>",
        "rtie": {
            "cropmode": "pixel",
            "quality":"<?=$quality?>",
            "scalemode":"<?=$scalemode?>",
            "enhance":"brightness:<?= $brightness; ?>,contrast:<?= $contrast; ?>,sharpness:<?= $sharpness; ?>,color:<?= $color; ?>"
        }
    };
    <?php }?>
    console.log(options)
				/*if(player) {
					player.destroy();
				}*/

    //var player = 
    THRONContentExperience("elementId_<?= $uniqID;  ?>", options);
</script>