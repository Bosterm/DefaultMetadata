<div class="field">
	<div class="two columns alpha">
		<label for="can_admins_edit">Allow Admin Access</label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			Check this field to allow Admin Users to add or edit default values in addition to Super Users.
		</p>
		<input type="hidden" name="can_admins_edit" value="0"><input type="checkbox" name="can_admins_edit" id="can_admins_edit" value="1" <?php if(get_option('can_admins_edit')) {echo 'checked="checked"';} ?>>
	</div>
</div>