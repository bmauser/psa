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
 * @version $Id: Psa_Hook_Before_User_Create.php 86 2013-07-23 17:59:39Z bmauser $
 */


/**
 * Abstract class for <i>Psa_Hook_Before_User_Create</i> hooks.
 *
 * You can create a new user by passing '<kbd>new</kbd>' to {@link Psa_User} class constructor. See
 * {@link Psa_User::__construct()} method of {@link Psa_User} class.
 *
 * Before a new user is to be created with {@link Psa_User::save()} method,
 * <kbd>psa_main()</kbd> method of the hook object that extends this class will be called.
 * You can write <i>Psa_Hook_Before_User_Create</i> hooks by extending this class.
 * <br><br><b>Example:</b>
 *
 * <code>
 * <?php
 * class my_hook extends Psa_Hook_Before_User_Create{
 *
 *     // you have to define psa_main() method in your child class
 *     function psa_main($user){
 *
 *         // set some property to new user
 *         $user->favorite_background_color = 'white';
 *     }
 * }
 * ?>
 * </code>
 *
 * When this example hook is registered every new created user will have property called
 * <kbd>favorite_background_color</kbd> with value '<kbd>white</kbd>'.
 *
 * <b>Note:</b> this hook is called before the new user is created and saved to database. Because of that
 * this hook will be called even if the user is not created due to some error like user with the same userneme
 * already exists in the database. If you set some property to user like <i>favorite_background_color</i> in
 * example above and user is successfully created this property will be saved to the database and you don't have to call
 * {@link Psa_User::save()} method later to store it. That is unlike {@link Psa_Hook_After_User_Create} hook.
 * Also, a user ID is unknown when this hook is called and is set after the user is created.
 *
 *
 * @see Psa_User::save()
 * @see Psa_Hook_After_User_Create
 */
abstract class Psa_Hook_Before_User_Create extends Psa_Model{

	/**
	 * This method will be called before the new user is
	 * created with {@link Psa_User::save()} method.
	 *
	 * @param Psa_User $user_object Reference to the {@link Psa_User} object whose
	 * {@link Psa_User::save()} method is invoked.
	 */
	abstract protected function psa_main($user_object);
}
