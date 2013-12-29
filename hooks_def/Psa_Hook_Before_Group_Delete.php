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
 * @package PSA
 * @subpackage hooks
 * @version $Id: Psa_Hook_Before_Group_Delete.php 86 2013-07-23 17:59:39Z bmauser $
 */


/**
 * Abstract class for hooks type of <i>Psa_Hook_Before_Group_Delete</i>.
 *
 * Before a group is deleted from the database with {@link psa_delete_group()} function,
 * <kbd>psa_main()</kbd> method of the hook object that extends this class will be called.
 * You can write <i>Psa_Hook_Before_Group_Delete</i> hooks by extending this class.
 * <br><br><b>Example:</b>
 *
 * <code>
 * <?php
 * class my_hook extends Psa_Hook_Before_Group_Delete{
 *
 *     // you have to define psa_main() method in your child class
 *     function psa_main($group){
 *
 *         // this makes sense if $group is group ID
 *         $group = new Psa_Group($group);
 *
 *         // delete folder associated with the group
 *         rmdir ('/var/groups/' . $group->name);
 *     }
 * }
 * ?>
 * </code>
 *
 * When this example hook is registered, before group deletion with {@link psa_delete_group()} function,
 * folder associated with the group will be deleted.
 *
 * <b>Note:</b> argument passed to the <kbd>psa_main()</kbd> method can be an integer (group ID) or a string (group name) this depends on
 * what you pass to {@link psa_delete_group()} function.
 *
 * @see Psa_Group
 * @see Psa_Hook_After_Group_Delete
 */
abstract class Psa_Hook_Before_Group_Delete extends Psa_Model{


	/**
	 * This method will be called before the group is deleted from
	 * the database with {@link psa_delete_group()} function.
	 *
	 * @param int|string $group ID or name of the group to be deleted.
	 * Same as passed to {@link psa_delete_group()} function.
	 * @see psa_delete_group()
	 */
	abstract protected function psa_main($group);
}
