<?
	$title_prefix = 'Price Comparisons';
	require('model_header.php');

	echo getTabHeader(URL_EQUIPMENT_MODEL, $asin, $man, $model, $count_similar);
?>

	<script language="javascript" src="http://ah.pricegrabber.com/cb_table.php?masterid=<?=$row[pg_masterid]?>&dw=1&cobrand_id=718&vw=2&sml=1&rst=1&sblpt=1&slp=1&olt=1&w=100&pgb=1&sbt=1&ssbox=1&ss=1&l=20"></script>

						</td></tr>
					</table>
				</td></tr>
			</table>
		
		</td>
	</tr></table><!-- End of table for sidebar ad -->

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
