<?hh // strict
/*
 *  Copyright (c) 2015, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace Facebook\DefinitionFinder\Test;

use Facebook\DefinitionFinder\FileParser;

class GenericsTest extends \PHPUnit_Framework_TestCase {
  public function testClassHasGenerics(): void {
    $data = '<?hh class Foo<Tk, Tv> {}';
    $parser = FileParser::FromData($data);
    $class = $parser->getClass('Foo');

    $this->assertEquals(
      Vector { 'Tk', 'Tv' },
      $class->getGenericTypes()->map($x ==> $x->getName()),
    );

    $this->assertEquals(
      Vector { null, null },
      $class->getGenericTypes()->map($x ==> $x->getConstraint()),
    );
  }

  public function testFunctionHasGenerics(): void {
    $data = '<?hh function foo<Tk, Tv>(){}';
    $parser = FileParser::FromData($data);
    $function = $parser->getFunction('foo');

    $this->assertEquals(
      Vector { 'Tk', 'Tv' },
      $function->getGenericTypes()->map($x ==> $x->getName()),
    );

    $this->assertEquals(
      Vector { null, null },
      $function->getGenericTypes()->map($x ==> $x->getConstraint()),
    );
  }

  public function testConstrainedGenerics(): void {
    $data = '<?hh class Foo<Tk as Bar> {}';
    $parser = FileParser::FromData($data);
    $class = $parser->getClass('Foo');

    $this->assertEquals(
      Vector { 'Bar' },
      $class->getGenericTypes()->map($x ==> $x->getConstraint()),
    );
  }

  public function testVariance(): void {
    $data = '<?hh class Foo<-Ta, Tb, +Tc> {}';
    $parser = FileParser::FromData($data);
    $class = $parser->getClass('Foo');
    $generics = $class->getGenericTypes();

    $this->assertEquals(
      Vector { 'Ta', 'Tb', 'Tc' },
      $generics->map($x ==> $x->getName()),
    );
    $this->assertEquals(
      Vector { true, false, false },
      $generics->map($x ==> $x->isContravariant()),
    );
    $this->assertEquals(
      Vector { false, true, false },
      $generics->map($x ==> $x->isInvariant()),
    );
    $this->assertEquals(
      Vector { false, false, true},
      $generics->map($x ==> $x->isCovariant()),
    );
  }
}