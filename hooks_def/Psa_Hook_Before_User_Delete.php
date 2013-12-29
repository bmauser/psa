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
 * @version $Id: Psa_Hook_Before_User_Delete.php 86 2013-07-23 17:59:39Z bmauser $
 */


/**
 * Abstract class for <i>Psa_Hook_Before_User_Delete</i> hooks.
 *
 * Before the user is deleted from the database with {@link psa_delete_user()} function,
 * <kbd>psa_main()</kbd> method of the hook object that extends this class will be called.
 * You can write <i>Psa_Hook_Before_User_Delete</i> hooks by extending this class.
 * <br><br><b>Example:</b>
 *
 * <code>
 * <?php
 * class my_hook extends Psa_Hook_Before_User_Delete{
 *
 *     // you have to define psa_main() method in your child class
 *     function psa_main($user){
 *
 *         // this makes sense if $user is user ID
 *         $user = new Psa_User($user);
 *         $user->restore();
 *
 *         // delete folder associated with the user
 *         rmdir ('/var/users/' . $user->username);
 *     }
 * }
 * ?>
 * </code>
 *
 * When this example hook is registered, before user deletion with {@link psa_delete_user()} function,
 * folder associated with user will be deleted.
 *
 * <b>Note:</b> argument passed to the <kbd>psa_main()</kbd> method can be an integer (user ID) or a string (username). It depends on
 * what you pass to {@link psa_delete_user()} function.
 *
 * @see Psa_User
 * @see Psa_Hook_After_User_Delete
 */
abstract class Psa_Hook_Before_User_Delete extends Psa_Model{


	/**
	 * This method will be called before the user is deleted from
	 * the database with {@link psa_delete_user()} function.
	 *
	 * @param int|string $user ID or username of the user to be deleted.
	 * Same as passed to {@link psa_delete_user()} function.
	 * @see psa_delete_user()
	 */
	abstract protected function psa_main($user);
}
