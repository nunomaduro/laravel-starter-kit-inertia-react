---
id: solid-lsp-contracts
title: SOLID - Liskov Substitution (Contracts)
category: solid-principles
priority: critical
tags: [SOLID, LSP, liskov-substitution, contracts]
related: [solid-lsp-preconditions, solid-ocp-abstraction, core-composition]
---

# Liskov Substitution Principle - Contracts

Subtypes must be substitutable for their base types without altering the correctness of the program. Derived classes must honor the contracts established by their base classes.

## Bad Example

```typescript
// Anti-pattern: Subclass violates the contract of the base class

class Rectangle {
  protected _width: number;
  protected _height: number;

  constructor(width: number, height: number) {
    this._width = width;
    this._height = height;
  }

  get width(): number {
    return this._width;
  }

  set width(value: number) {
    this._width = value;
  }

  get height(): number {
    return this._height;
  }

  set height(value: number) {
    this._height = value;
  }

  getArea(): number {
    return this._width * this._height;
  }
}

// Square violates LSP - it changes the behavior of setters
class Square extends Rectangle {
  constructor(side: number) {
    super(side, side);
  }

  // Violates LSP: setter has different behavior than parent
  set width(value: number) {
    this._width = value;
    this._height = value; // Unexpected side effect!
  }

  set height(value: number) {
    this._width = value; // Unexpected side effect!
    this._height = value;
  }
}

// This code works with Rectangle but breaks with Square
function resizeRectangle(rect: Rectangle): void {
  rect.width = 10;
  rect.height = 5;

  // Expects area to be 50, but Square gives 25!
  console.assert(rect.getArea() === 50, 'Area should be 50');
}

const rectangle = new Rectangle(4, 4);
resizeRectangle(rectangle); // Works: area is 50

const square = new Square(4);
resizeRectangle(square); // Fails: area is 25, not 50
```

## Good Example

```typescript
// Correct approach: Use composition and proper abstractions

// Define what shapes can do
interface Shape {
  getArea(): number;
  getPerimeter(): number;
}

// Define what resizable shapes can do
interface ResizableShape extends Shape {
  scale(factor: number): void;
}

// Immutable rectangle - no setters that could be violated
class Rectangle implements Shape {
  constructor(
    readonly width: number,
    readonly height: number
  ) {
    if (width <= 0 || height <= 0) {
      throw new Error('Dimensions must be positive');
    }
  }

  getArea(): number {
    return this.width * this.height;
  }

  getPerimeter(): number {
    return 2 * (this.width + this.height);
  }

  // Return new instance instead of mutating
  withWidth(width: number): Rectangle {
    return new Rectangle(width, this.height);
  }

  withHeight(height: number): Rectangle {
    return new Rectangle(this.width, height);
  }

  scale(factor: number): Rectangle {
    return new Rectangle(this.width * factor, this.height * factor);
  }
}

// Square is its own shape, not a subtype of Rectangle
class Square implements Shape {
  constructor(readonly side: number) {
    if (side <= 0) {
      throw new Error('Side must be positive');
    }
  }

  getArea(): number {
    return this.side * this.side;
  }

  getPerimeter(): number {
    return 4 * this.side;
  }

  withSide(side: number): Square {
    return new Square(side);
  }

  scale(factor: number): Square {
    return new Square(this.side * factor);
  }
}

// Functions work with the Shape interface
function printShapeInfo(shape: Shape): void {
  console.log(`Area: ${shape.getArea()}`);
  console.log(`Perimeter: ${shape.getPerimeter()}`);
}

// This works correctly with both Rectangle and Square
const rect = new Rectangle(10, 5);
printShapeInfo(rect); // Area: 50, Perimeter: 30

const square = new Square(5);
printShapeInfo(square); // Area: 25, Perimeter: 20

// For operations specific to rectangles, use Rectangle type
function createBanner(width: number, height: number): Rectangle {
  return new Rectangle(width, height);
}

// For operations that work with any shape, use Shape interface
function calculateTotalArea(shapes: Shape[]): number {
  return shapes.reduce((total, shape) => total + shape.getArea(), 0);
}

const shapes: Shape[] = [
  new Rectangle(10, 5),
  new Square(4),
  new Rectangle(3, 7)
];

console.log(calculateTotalArea(shapes)); // 50 + 16 + 21 = 87
```

## Why

1. **Predictable Behavior**: Code using the base type works correctly with any subtype. No surprises.

2. **Safe Polymorphism**: You can pass any `Shape` to functions expecting `Shape` without checking the concrete type.

3. **Contract Honoring**: Each class fully honors its interface contract - `getArea()` always returns the correct area.

4. **Immutability Benefits**: By making shapes immutable, we avoid the setter problem entirely.

5. **Proper Modeling**: Square and Rectangle are separate concepts that happen to share behavior (Shape), not a parent-child relationship.

6. **Easier Testing**: Tests for `Shape` work for all implementations. No special cases needed.

7. **Design Clarity**: The inheritance hierarchy reflects true "is-a" relationships, not just code reuse.
