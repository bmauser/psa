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
 * @version $Id: Psa_Hook_Before_Group_Create.php 86 2013-07-23 17:59:39Z bmauser $
 */


/**
 * Abstract class for <i>Psa_Hook_Before_Group_Create</i> hooks.
 *
 * You can write <i>Psa_Hook_Before_Group_Create</i> hooks by extending this class.
 * You can create a new group by passing '<kbd>new</kbd>' to {@link Psa_Group} class constructor. See
 * {@link Psa_Group::__construct()} method of {@link Psa_Group} class.
 *
 * Before a new group is successfully created with {@link Psa_Group::save()} method,
 * <kbd>psa_main()</kbd> method of the hook object that extends this class will be called.
 * <br><br><b>Example:</b>
 *
 * <code>
 * <?php
 * class my_hook extends Psa_Hook_Before_Group_Create{
 *
 *     // you have to define psa_main() method in your child class
 *     function psa_main($group){
 *
 *         // set some property to the new group
 *         $group->good_students = 'yes';
 *     }
 * }
 * ?>
 * </code>
 *
 * When this example hook is registered, every new created group will have a property called
 * <kbd>good_students</kbd> with value '<kbd>yes</kbd>'.
 *
 * <b>Note:</b> this hook is called before a new group is created and saved to the database. Because of that,
 * this hook will be called even if the group is not created due to some error, ex. group with the same name
 * already exists in the database. If you set some property to the group like <i>good_students</i> in the
 * example above and the group is successfully created, this property will be saved to the database and you don't have to call
 * {@link Psa_Group::save()} method later to store it. That is unlike {@link Psa_Hook_After_Group_Create} hook.
 * Also, a group ID is unknown when this hook is called and it is set after the group is created.
 *
 * @see Psa_Group::save()
 * @see Psa_Hook_Before_User_Create
 */
abstract class Psa_Hook_Before_Group_Create extends Psa_Model{

	/**
	 * This method will be called before a new group is 
	 * created with {@link Psa_Group::save()} method.
	 *
	 * @param Psa_Group $group_object Reference to the {@link Psa_Group} object whose {@link Psa_Group::save()} method is invoked.
	 * @see Psa_Group::save()
	 */
	abstract protected function psa_main($group_object);
}
