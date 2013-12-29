<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Bojan Mauser
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @link http://code.google.com/p/phpstartapp/
 * @author Bojan Mauser <bmauser@gmail.com>
 * @package PSA\hooks
 * @version $Id: Psa_Hook_After_Group_Delete.php 86 2013-07-23 17:59:39Z bmauser $
 */


/**
 * Abstract class for <i>Psa_Hook_After_Group_Delete</i> hooks.
 *
 * You can write <i>Psa_Hook_After_Group_Delete</i> hooks by extending this class.
 * After the group is successfully deleted from the database with {@link psa_delete_group()} function,
 * <kbd>psa_main()</kbd> method of the hook object that extends this class will be called.
 * <br><br><b>Example:</b>
 *
 * <code>
 * <?php
 * class my_hook extends Psa_Hook_After_Group_Delete{
 *
 *     // you have to define psa_main() method in your child class
 *     function psa_main($group){
 *
 *         // delete folder associated with the group
 *         rmdir ('/var/groups/group_' . $group);
 *     }
 * }
 * ?>
 * </code>
 *
 * When this example hook is registered, after group deletion with {@link psa_delete_group()} function,
 * folder associated with the group will be deleted.
 *
 * <b>Note:</b> the row in the database will be deleted before calling hook's <kbd>psa_main()</kbd> method.
 * So, you cannot read other information about the group from the database because database record doesn't
 * exist any more and all information you have is group ID or name passed as argument to <kbd>psa_main()</kbd> method.
 *
 * <b>Note:</b> the argument passed to the <kbd>psa_main()</kbd> method can be integer (group ID) or string (group name).
 * This depends on what you pass to {@link psa_delete_group()} function.
 *
 *
 * @see Psa_Group
 * @see Psa_Hook_Before_Group_Delete
 */
abstract class Psa_Hook_After_Group_Delete extends Psa_Model{


	/**
	 * This method will be called after the group is successfully deleted from the database with {@link psa_delete_group()} function.
	 *
	 * @param int|string $group ID or name of the deleted group as passed to {@link psa_delete_group()} function.
	 * @see psa_delete_group()
	 */
	abstract protected function psa_main($group);
}
