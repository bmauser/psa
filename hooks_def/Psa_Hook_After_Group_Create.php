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
 * @version $Id: Psa_Hook_After_Group_Create.php 86 2013-07-23 17:59:39Z bmauser $
 */


/**
 * Abstract class for <i>Psa_Hook_After_Group_Create</i> hook.
 *
 * You make <i>Psa_Hook_After_Group_Create</i> hooks by extending this class.
 * You can crate a new group by passing '<kbd>new</kbd>' to {@link Psa_Group} class constructor. See
 * {@link Psa_Group::__construct()} method.
 *
 * After a new group is successfully created with {@link Psa_Group::save()} method,
 * <kbd>psa_main()</kbd> method of the hook object that extends this class will be called.
 * <br><br><b>Example:</b>
 *
 * <code>
 * <?php
 * class my_hook extends Psa_Hook_After_Group_Create{
 *
 *     // you have to define psa_main() method in your child class
 *     function psa_main($group){
 *
 *         // set some property to new group
 *             $group->students = 'yes';
 *     }
 * }
 * ?>
 * </code>
 *
 * When this example hook is registered every new created group will have property called
 * <kbd>students</kbd> with value '<kbd>yes</kbd>'.
 *
 * <b>Note:</b> if you set some property to the group like <i>students</i> in the example above
 * it will not be saved until you call {@link Psa_Group::save()} method again.
 *
 *
 * @see Psa_Group::save()
 * @see Psa_Hook_After_User_Create
 */
abstract class Psa_Hook_After_Group_Create extends Psa_Model{

	/**
	 * This method will be called after a new group is successfully created with
	 * {@link Psa_Group::save()} method.
	 *
	 * @param Psa_Group $group_object Reference to the {@link Psa_Group} object
	 * whose {@link Psa_Group::save()} method is invoked.
	 */
	abstract protected function psa_main($group_object);

}
