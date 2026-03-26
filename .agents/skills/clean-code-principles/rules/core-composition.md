---
id: core-composition
title: Composition Over Inheritance
category: core-principles
priority: critical
tags: [composition, inheritance, flexibility, design]
related: [solid-srp-class, solid-dip-injection, core-encapsulation]
---

# Composition Over Inheritance

Favor composing objects from smaller, focused pieces over building deep inheritance hierarchies. Composition provides more flexibility, better encapsulation, and avoids the fragile base class problem.

## Bad Example

```typescript
// Anti-pattern: Deep inheritance hierarchy

class Animal {
  protected name: string;
  protected energy: number = 100;

  constructor(name: string) {
    this.name = name;
  }

  eat(amount: number): void {
    this.energy += amount;
    console.log(`${this.name} is eating. Energy: ${this.energy}`);
  }

  sleep(hours: number): void {
    this.energy += hours * 10;
    console.log(`${this.name} slept for ${hours} hours. Energy: ${this.energy}`);
  }
}

class Bird extends Animal {
  fly(): void {
    this.energy -= 20;
    console.log(`${this.name} is flying. Energy: ${this.energy}`);
  }
}

class Duck extends Bird {
  swim(): void {
    this.energy -= 5;
    console.log(`${this.name} is swimming. Energy: ${this.energy}`);
  }

  quack(): void {
    console.log(`${this.name} says quack!`);
  }
}

class FlyingFish extends Animal {
  // Problem: Can't inherit from both Bird and Fish
  // Must duplicate flying code or create awkward hierarchy

  swim(): void {
    this.energy -= 5;
    console.log(`${this.name} is swimming. Energy: ${this.energy}`);
  }

  // Duplicated from Bird class!
  fly(): void {
    this.energy -= 20;
    console.log(`${this.name} is flying. Energy: ${this.energy}`);
  }
}

class Penguin extends Bird {
  // Problem: Penguins can't fly but inherit fly()
  // Must override to throw error - LSP violation

  fly(): void {
    throw new Error('Penguins cannot fly!');
  }

  swim(): void {
    this.energy -= 5;
    console.log(`${this.name} is swimming. Energy: ${this.energy}`);
  }
}

// More problems:
// - What about a robot bird? It doesn't eat or sleep.
// - What about a bat? It flies but isn't a bird.
// - Every change to Animal affects all subclasses.
// - Testing requires understanding entire hierarchy.
```

## Good Example

```typescript
// Correct approach: Composition with focused behaviors

// Define behaviors as interfaces
interface Eater {
  eat(amount: number): void;
}

interface Sleeper {
  sleep(hours: number): void;
}

interface Flyer {
  fly(): void;
}

interface Swimmer {
  swim(): void;
}

interface Speaker {
  speak(): void;
}

// Implement behaviors as standalone classes
class StandardEater implements Eater {
  constructor(private entity: { name: string; energy: number }) {}

  eat(amount: number): void {
    this.entity.energy += amount;
    console.log(`${this.entity.name} is eating. Energy: ${this.entity.energy}`);
  }
}

class StandardSleeper implements Sleeper {
  constructor(private entity: { name: string; energy: number }) {}

  sleep(hours: number): void {
    this.entity.energy += hours * 10;
    console.log(`${this.entity.name} slept for ${hours} hours. Energy: ${this.entity.energy}`);
  }
}

class WingedFlyer implements Flyer {
  constructor(
    private entity: { name: string; energy: number },
    private energyCost: number = 20
  ) {}

  fly(): void {
    this.entity.energy -= this.energyCost;
    console.log(`${this.entity.name} is flying. Energy: ${this.entity.energy}`);
  }
}

class AquaticSwimmer implements Swimmer {
  constructor(
    private entity: { name: string; energy: number },
    private energyCost: number = 5
  ) {}

  swim(): void {
    this.entity.energy -= this.energyCost;
    console.log(`${this.entity.name} is swimming. Energy: ${this.entity.energy}`);
  }
}

// Compose animals from behaviors
class Duck implements Eater, Sleeper, Flyer, Swimmer, Speaker {
  public name: string;
  public energy: number = 100;

  private eater: Eater;
  private sleeper: Sleeper;
  private flyer: Flyer;
  private swimmer: Swimmer;

  constructor(name: string) {
    this.name = name;
    this.eater = new StandardEater(this);
    this.sleeper = new StandardSleeper(this);
    this.flyer = new WingedFlyer(this);
    this.swimmer = new AquaticSwimmer(this);
  }

  eat(amount: number): void {
    this.eater.eat(amount);
  }

  sleep(hours: number): void {
    this.sleeper.sleep(hours);
  }

  fly(): void {
    this.flyer.fly();
  }

  swim(): void {
    this.swimmer.swim();
  }

  speak(): void {
    console.log(`${this.name} says quack!`);
  }
}

// Penguin: swims but doesn't fly - no problem!
class Penguin implements Eater, Sleeper, Swimmer, Speaker {
  public name: string;
  public energy: number = 100;

  private eater: Eater;
  private sleeper: Sleeper;
  private swimmer: Swimmer;

  constructor(name: string) {
    this.name = name;
    this.eater = new StandardEater(this);
    this.sleeper = new StandardSleeper(this);
    this.swimmer = new AquaticSwimmer(this);
  }

  eat(amount: number): void {
    this.eater.eat(amount);
  }

  sleep(hours: number): void {
    this.sleeper.sleep(hours);
  }

  swim(): void {
    this.swimmer.swim();
  }

  speak(): void {
    console.log(`${this.name} says squawk!`);
  }
}

// Flying fish: swims and flies - easy!
class FlyingFish implements Swimmer, Flyer {
  public name: string;
  public energy: number = 100;

  private swimmer: Swimmer;
  private flyer: Flyer;

  constructor(name: string) {
    this.name = name;
    this.swimmer = new AquaticSwimmer(this);
    this.flyer = new WingedFlyer(this, 30); // Different energy cost
  }

  swim(): void {
    this.swimmer.swim();
  }

  fly(): void {
    this.flyer.fly();
  }
}

// Robot bird: flies but doesn't eat or sleep
class RobotBird implements Flyer, Speaker {
  public name: string;
  public energy: number = 100;

  private flyer: Flyer;

  constructor(name: string) {
    this.name = name;
    this.flyer = new WingedFlyer(this, 10); // Efficient robot
  }

  fly(): void {
    this.flyer.fly();
  }

  speak(): void {
    console.log(`${this.name} says BEEP BOOP!`);
  }

  recharge(): void {
    this.energy = 100;
    console.log(`${this.name} recharged to full energy.`);
  }
}

// Functions work with any entity that has the required behavior
function makeEntityFly(flyer: Flyer): void {
  flyer.fly();
}

function feedEntity(eater: Eater, amount: number): void {
  eater.eat(amount);
}

// Works with duck, flying fish, or robot bird
makeEntityFly(new Duck('Donald'));
makeEntityFly(new FlyingFish('Nemo'));
makeEntityFly(new RobotBird('R2D2'));

// Works with duck or penguin, but not robot bird (correctly!)
feedEntity(new Duck('Donald'), 50);
feedEntity(new Penguin('Pingu'), 50);
// feedEntity(new RobotBird('R2D2'), 50); // Type error - RobotBird isn't an Eater
```

## Why

1. **Flexibility**: Compose any combination of behaviors. No artificial hierarchy constraints.

2. **Avoids Diamond Problem**: No multiple inheritance issues. Just implement multiple interfaces.

3. **LSP Compliance**: No need to override methods to throw errors. Types only have methods they actually support.

4. **Reusability**: Behaviors can be reused across unrelated types.

5. **Testability**: Test behaviors in isolation. Mock specific behaviors easily.

6. **Runtime Flexibility**: Can change behaviors at runtime by swapping implementations.

7. **Stable Dependencies**: Behavior implementations are stable. Adding new composed types doesn't affect existing code.
