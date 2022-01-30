</div>
<footer>
<!--	<span class="copyright"><?php echo $copyright;?></span> -->
</footer>
<script src="<?php echo '/assets/js/fnct.js';?>" type="application/javascript"></script>
<?php if(SKIN != 'original'){?>
<script src="<?php echo '/assets/'.SKIN.'/js/skin.js';?>" type="application/javascript"></script>
<?php }?>
<script><?php if(isset($JSini)){echo $JSini;}?></script>
</body>
</html>