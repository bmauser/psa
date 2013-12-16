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
 * @version $Id: Psa_Hook_After_User_Delete.php 86 2013-07-23 17:59:39Z bmauser $
 */


/**
 * Abstract class for <i>Psa_Hook_After_User_Delete</i> hooks.
 *
 * You write <i>Psa_Hook_After_User_Delete</i> hooks by extending this class.
 * After the user is successfully deleted from the database with {@link psa_delete_user()} function,
 * <kbd>psa_main()</kbd> method of the hook object that extends this class will be called.
 * <br><br><b>Example:</b>
 *
 * <code>
 * <?php
 * class my_hook extends Psa_Hook_After_User_Delete{
 *
 *     // you have to define psa_main() method in your child class
 *     function psa_main($user){
 *
 *         // delete folder associated with the user
 *         rmdir ('/var/users/user_' . $user);
 *     }
 * }
 * ?>
 * </code>
 *
 * When this example hook is registered, after every user deletion with {@link psa_delete_user()} function
 * folder associated with user will be deleted.
 *
 * <b>Note:</b> the record about the user in the database will be deleted before calling hook's <kbd>psa_main()</kbd> method.
 * So you cannot read other information about the user from database (authorize user) because database record doesn't
 * exists and all information you have is user ID or username passed as argument to <kbd>psa_main()</kbd> method.
 *
 * <b>Note:</b> the argument passed to the <kbd>psa_main()</kbd> method can be integer (user ID) or string (username) this depends on
 * what you pass to {@link psa_delete_user()} function.
 *
 *
 * @see Psa_User
 * @see Psa_Hook_Before_User_Delete
 */
abstract class Psa_Hook_After_User_Delete extends Psa_Model{


	/**
	 * This method will be called after the user is successfully deleted from database with {@link psa_delete_user()} function.
	 * @param int|string $user ID or username name of the deleted user. Same as passed to {@link psa_delete_user()} function.
	 * @see psa_delete_user()
	 */
	abstract protected function psa_main($user);
}
