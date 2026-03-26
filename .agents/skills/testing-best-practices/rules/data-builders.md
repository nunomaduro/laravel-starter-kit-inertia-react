---
title: Test Data Builders
impact: HIGH
impactDescription: "complex test data readability and maintainability"
tags: test-data, builder-pattern, fluent-api
---

## Test Data Builders

**Impact: HIGH (complex test data readability and maintainability)**

Use the builder pattern for complex test data with fluent, chainable configuration. Builders are ideal for objects with many optional configurations, while factories work better for simpler objects with few variations.

## Incorrect

```typescript
// ❌ Bad: Complex nested object construction is hard to read and maintain
describe('ReportGenerator', () => {
  test('generates monthly sales report', () => {
    const report = {
      id: 'report-1',
      type: 'monthly-sales',
      dateRange: {
        start: new Date('2024-01-01'),
        end: new Date('2024-01-31')
      },
      filters: {
        regions: ['US', 'EU'],
        categories: ['electronics'],
        minAmount: 100,
        maxAmount: null,
        includeRefunds: false,
        excludeTestOrders: true
      },
      groupBy: ['region', 'category'],
      sortBy: { field: 'totalSales', direction: 'desc' },
      format: {
        type: 'pdf',
        includeCharts: true,
        chartTypes: ['bar', 'pie'],
        pageSize: 'A4',
        orientation: 'landscape'
      },
      recipients: [
        { email: 'manager@example.com', name: 'Manager' }
      ],
      schedule: {
        frequency: 'monthly',
        dayOfMonth: 1,
        time: '09:00'
      }
    };

    expect(generator.canGenerate(report)).toBe(true);
  });
});
```

**Problems:**
- Large inline object literals obscure what the test is actually verifying
- Irrelevant properties clutter the test making it hard to spot the key data
- Duplicating the same structure across tests is error-prone
- Changes to the object shape require updates in every test

## Correct

```typescript
// ✅ Good: Builder pattern for fluent test data construction
class ReportBuilder {
  private report: Partial<Report> = {
    id: `report-${Date.now()}`,
    type: 'basic',
    filters: {},
    format: { type: 'pdf' }
  };

  static create(): ReportBuilder {
    return new ReportBuilder();
  }

  ofType(type: ReportType): this {
    this.report.type = type;
    return this;
  }

  forMonth(year: number, month: number): this {
    const start = new Date(year, month - 1, 1);
    const end = new Date(year, month, 0);
    this.report.dateRange = { start, end };
    return this;
  }

  forRegions(...regions: string[]): this {
    this.report.filters = { ...this.report.filters, regions };
    return this;
  }

  withCharts(...chartTypes: ChartType[]): this {
    this.report.format = {
      ...this.report.format,
      includeCharts: true,
      chartTypes
    };
    return this;
  }

  sendTo(email: string, name?: string): this {
    this.report.recipients = [
      ...(this.report.recipients || []),
      { email, name: name ?? email.split('@')[0] }
    ];
    return this;
  }

  scheduledMonthly(dayOfMonth: number, time: string): this {
    this.report.schedule = {
      frequency: 'monthly',
      dayOfMonth,
      time
    };
    return this;
  }

  build(): Report {
    return this.report as Report;
  }
}

// Preset builders for common scenarios
class MonthlySalesReportBuilder extends ReportBuilder {
  constructor() {
    super();
    this.ofType('monthly-sales')
      .groupedBy('region', 'category')
      .sortedBy('totalSales', 'desc')
      .asPdf({ orientation: 'landscape' });
  }

  static create(): MonthlySalesReportBuilder {
    return new MonthlySalesReportBuilder();
  }
}

// Clean, readable tests using builders
describe('ReportGenerator', () => {
  test('generates basic monthly sales report', () => {
    const report = ReportBuilder.create()
      .ofType('monthly-sales')
      .forMonth(2024, 1)
      .build();

    expect(generator.canGenerate(report)).toBe(true);
  });

  test('generates regional sales report with charts', () => {
    const report = MonthlySalesReportBuilder.create()
      .forMonth(2024, 1)
      .forRegions('US', 'EU')
      .withCharts('bar', 'pie')
      .build();

    const result = generator.generate(report);

    expect(result.pages).toHaveLength(2);
    expect(result.charts).toHaveLength(2);
  });

  test('schedules and emails monthly report', () => {
    const report = MonthlySalesReportBuilder.create()
      .forMonth(2024, 1)
      .sendTo('manager@example.com', 'Sales Manager')
      .sendTo('ceo@example.com', 'CEO')
      .scheduledMonthly(1, '09:00')
      .build();

    expect(report.recipients).toHaveLength(2);
    expect(report.schedule?.frequency).toBe('monthly');
  });
});
```

**Benefits:**
- Fluent API with chainable methods reads like natural language
- Method names explain what each configuration does (self-documenting)
- Tests build only the data they need, nothing more
- IDE autocomplete shows available configuration options
- Base builders can be extended for common scenarios
- Changes to data structure are isolated to the builder class

Reference: [Test Data Builder Pattern](https://wiki.c2.com/?TestDataBuilder)
