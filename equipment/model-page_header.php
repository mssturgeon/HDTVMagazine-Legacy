<div style="height:250px; ">
	<div id="eq-image"><?=$img_amazon?></div>
	<div id="eq-title"><?=$title?></div>
	Other products by <a href="<?=$url_man?>"><?=$man?></a><br />
	<?=$img_rating?> (<a href="model-reviews.php?<?=$query_string?>"><?=$row['TotalReviews']?> reviews</a>)
	<div class="dottedline" style="display:block;width:100%"></div>
	<div id="eq-pricing">
		<a target="_blank" href="<?=$row['DetailPageURL']?>"><img src="/images/buy_amazon.gif" alt="Buy from Amazon" align="right" /></a>
		<div class="primary_bold_10">Amazon Pricing and Availability*</div>
		As of: <?=$as_of?><br />
		<?
			echo $price_list;
			echo $price_new;
			echo $price_refurb;
			echo $price_used;
		?>
		<br />
	</div>
	<span style="clear:both; font-size:.8em;">* Price is accurate as of the date/time indicated. Prices and product availability are subject to change. 
	Any price displayed on the Amazon web site at the time of purchase will govern the sale of this product.</span>
</div>
