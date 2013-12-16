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
 * @version $Id: Psa_Hook_After_User_Authorize.php 86 2013-07-23 17:59:39Z bmauser $
 */


/**
 * Abstract class for <i>Psa_Hook_After_User_Authorize</i> hooks.
 *
 * After a user is successfully authorized with {@link Psa_User::authorize()} method,
 * <kbd>psa_main()</kbd> method of the hook object that extends this class will be called.
 * You can write <i>Psa_Hook_After_User_Authorize</i> hooks by extending this class.
 * <br><br><b>Example:</b>
 *
 * <code>
 * <?php
 * class my_hook extends Psa_Hook_After_User_Authorize{
 *
 *     // you have to define psa_main() method in your child class
 *     function psa_main($user){
 *
 *         // set some property to authorized user
 *         $user->favorite_background_color = 'white';
 *
 *         $user->save();
 *     }
 * }
 * ?>
 * </code>
 *
 * When this example hook is registered every authorized user will have property called
 * <kbd>favorite_background_color</kbd> with value '<kbd>white</kbd>'.
 *
 * @see Psa_User
 * @see Psa_User::authorize()
 */
abstract class Psa_Hook_After_User_Authorize extends Psa_Model{

	/**
	 * This method will be called after a user is successfully authorized
	 * with {@link Psa_User::authorize()} method.
	 *
	 * @param Psa_User $user_object Reference to the {@link Psa_User} object
	 * whose {@link Psa_User::authorize()} method is invoked.
	 * @see Psa_User::authorize()
	 */
	abstract protected function psa_main($user_object);
}
