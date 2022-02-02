<h2><?php _e( 'Import', 'filebird' ); ?></h2>
<p style="margin-top:0;">
  <?php _e( 'Import categories/folders from other plugins. We import virtual folders, your website will be safe, don\'t worry ;)', 'filebird' ); ?>
</p>
  <table class="form-table">
	  <tbody>
		  <tr class="<?php echo $countEnhancedFolder <= 3 ? 'hidden' : ''; ?>">
			  <th scope="row">
				<label for="">
				  <?php echo __( 'Enhanced Media Library plugin by wpUXsolutions', 'filebird' ); ?>
				</label>
			  </th>
			  <td>
				<?php if ( $countEnhancedFolder > 0 ) : ?>
				  <button class="button button-primary button-large njt-fb-import njt-button-loading" data-site="enhanced" type="button" data-count="<?php echo $countEnhancedFolder; ?>"><?php _e( 'Import Now', 'filebird' ); ?></button>
				<?php endif; ?>
				<p class="description" style="font-weight: 400">
				  <?php
					$str = __( 'We found you have <strong>(%1$s)</strong> categories you created from <strong>Enhanced Media Library</strong> plugin.', 'filebird' );
					if ( $countEnhancedFolder > 0 ) {
						$str .= __( ' Would you like to import to <strong>FileBird</strong>?', 'filebird' );
					}
					echo ( sprintf( $str, $countEnhancedFolder ) );
					?>
				</p>
			  </td>
		  </tr>
		  <tr class="<?php echo $countWpmlfFolder <= 3 ? 'hidden' : ''; ?>">
			  <th scope="row">
				<label for="">
				  <?php echo __( 'WordPress Media Library Folders by Max Foundry', 'filebird' ); ?>
				</label>
			  </th>
			  <td>
				<?php if ( $countWpmlfFolder > 0 ) : ?>
				  <button class="button button-primary button-large njt-fb-import njt-button-loading" data-site="wpmlf" type="button" data-count="<?php echo $countWpmlfFolder; ?>"><?php _e( 'Import Now', 'filebird' ); ?></button>
				  <?php endif; ?>
				<p class="description" style="font-weight: 400">
				  <?php
					$str = __( 'We found you have <strong>(%1$s)</strong> categories you created from <strong>WordPress Media Library Folders</strong> plugin.', 'filebird' );
					if ( $countWpmlfFolder > 0 ) {
						$str .= __( ' Would you like to import to <strong>FileBird</strong>?', 'filebird' );
					}
					echo ( sprintf( $str, $countWpmlfFolder ) );
					?>
				</p>
			  </td>
		  </tr>
		  <tr class="<?php echo $countWpmfFolder <= 3 ? 'hidden' : ''; ?>">
			  <th scope="row">
				<label for="">
				  <?php echo __( 'WP Media folder by Joomunited', 'filebird' ); ?>
				</label>
			  </th>
			  <td>
				<?php if ( $countWpmfFolder > 0 ) : ?>
				  <button class="button button-primary button-large njt-fb-import njt-button-loading" data-site="wpmf" type="button" data-count="<?php echo $countWpmfFolder; ?>"><?php _e( 'Import Now', 'filebird' ); ?></button>
				  <?php endif; ?>
				<p class="description" style="font-weight: 400">
				  <?php
					$str = __( 'We found you have <strong>(%1$s)</strong> categories you created from <strong>WP Media folder</strong> plugin.', 'filebird' );
					if ( $countWpmfFolder > 0 ) {
						$str .= __( ' Would you like to import to <strong>FileBird</strong>?', 'filebird' );
					}
					echo ( sprintf( $str, $countWpmfFolder ) );
					?>
				</p>
			  </td>
		  </tr>
		  <tr class="<?php echo $countRealMediaFolder <= 3 ? 'hidden' : ''; ?>">
			  <th scope="row">
				<label for="">
				  <?php echo __( 'WP Real Media Library by devowl.io GmbH', 'filebird' ); ?>
				</label>
			  </th>
			  <td>
				<?php if ( $countRealMediaFolder > 0 ) : ?>
				  <button class="button button-primary button-large njt-fb-import njt-button-loading" data-site="realmedia" type="button" data-count="<?php echo $countRealMediaFolder; ?>"><?php _e( 'Import Now', 'filebird' ); ?></button>
				  <?php endif; ?>
				<p class="description" style="font-weight: 400">
				  <?php
					$str = __( 'We found you have <strong>(%1$s)</strong> categories you created from <strong>WP Real Media Library</strong> plugin.', 'filebird' );
					if ( $countRealMediaFolder > 0 ) {
						$str .= __( ' Would you like to import to <strong>FileBird</strong>?', 'filebird' );
					}
					echo ( sprintf( $str, $countRealMediaFolder ) );
					?>
				</p>
			  </td>
		  </tr>
		  <tr class="<?php echo $countHappyFiles <= 3 ? 'hidden' : ''; ?>">
			  <th scope="row">
				<label for="">
				  <?php echo __( 'HappyFiles by Codeer', 'filebird' ); ?>
				</label>
			  </th>
			  <td>
				<?php if ( $countHappyFiles > 0 ) : ?>
				  <button class="button button-primary button-large njt-fb-import njt-button-loading" data-site="happyfiles" type="button" data-count="<?php echo $countHappyFiles; ?>"><?php _e( 'Import Now', 'filebird' ); ?></button>
				  <?php endif; ?>
				<p class="description" style="font-weight: 400">
				  <?php
					$str = __( 'We found you have <strong>(%1$s)</strong> categories you created from <strong>HappyFiles</strong> plugin.', 'filebird' );
					if ( $countHappyFiles > 0 ) {
						$str .= __( ' Would you like to import to <strong>FileBird</strong>?', 'filebird' );
					}
					echo ( sprintf( $str, $countHappyFiles ) );
					?>
				</p>
			  </td>
		  </tr>
		  <tr class="<?php echo $countPremioFolder <= 3 ? 'hidden' : ''; ?>">
			  <th scope="row">
				<label for="">
				  <?php echo __( 'Folders by Premio', 'filebird' ); ?>
				</label>
			  </th>
			  <td>
				<?php if ( $countPremioFolder > 0 ) : ?>
				  <button class="button button-primary button-large njt-fb-import njt-button-loading" data-site="premio" type="button" data-count="<?php echo $countPremioFolder; ?>"><?php _e( 'Import Now', 'filebird' ); ?></button>
				  <?php endif; ?>
				<p class="description" style="font-weight: 400">
				  <?php
					$str = __( 'We found you have <strong>(%1$s)</strong> categories you created from <strong>Folders</strong> plugin.', 'filebird' );
					if ( $countPremioFolder > 0 ) {
						$str .= __( ' Would you like to import to <strong>FileBird</strong>?', 'filebird' );
					}
					echo ( sprintf( $str, $countPremioFolder ) );
					?>
				</p>
			  </td>
		  </tr>
	  </tbody>
  </table>
<div class="fbv-row-breakline" style="padding: 15px 0">
	<span class="fbv-breakline"></span>
</div>
<h2><?php _e( 'Export', 'filebird' ); ?></h2>
<table class="form-table">
	  <tbody>
	  <tr>
			  <th scope="row">
				<label for="">
				  <?php echo __( 'Export CSV', 'filebird' ); ?>
				</label>
			  </th>
			<td>
		  <div style="display: flex; align-items: center">
			<button class="button button-primary button-large njt-fb-csv-export njt-button-loading" type="button">
			  <?php _e( 'Export Now', 'filebird' ); ?>
			</button>
			<a id="njt-fb-download-csv" href="javascript:;" style="margin-left: 10px" class="hidden">Download File</a>
		  </div>
		  <p class="description" style="font-weight: 400">
			<?php echo __( 'The current folder structure will be exported.', 'filebird' ); ?>
		</p>
			  </td>
		  </tr>
	  <tr>
			  <th scope="row">
				<label for="">
				  <?php echo __( 'Import CSV', 'filebird' ); ?>	
				</label>
			  </th>
			<td>
		  <div style="display: flex; align-items: center">            
			<input type="file" accept=".csv" id="njt-fb-upload-csv" name="csv_file" style="padding: 5px 0">
			<button class="button button-large njt-fb-csv-import hidden njt-button-loading" type="button">
			  <?php _e( 'Import Now', 'filebird' ); ?>
			</button>
		  </div>
			  <p class="description">
			  <?php echo __( 'Choose FileBird CSV file to import.', 'filebird' ); ?><br/>
			  <?php echo __( '(Please check to make sure that there is no duplicated name. The current folder structure is preserved.)', 'filebird' ); ?><br/>
			</p>
			</td>
		  </tr>
	  </tbody>
  </table>
