---
id: solid-ocp-abstraction
title: SOLID - Open/Closed (Abstraction)
category: solid-principles
priority: critical
tags: [SOLID, OCP, open-closed, abstraction]
related: [solid-ocp-extension, solid-dip-abstractions, pattern-repository]
---

# Open/Closed Principle - Abstraction

Use abstractions (interfaces and abstract classes) to define stable contracts that allow new implementations without modifying existing code.

## Bad Example

```typescript
// Anti-pattern: Concrete dependencies that require modification for new requirements
class ReportGenerator {
  generateReport(data: SalesData[], format: string): string {
    let report = '';

    // Calculate totals
    const totalSales = data.reduce((sum, d) => sum + d.amount, 0);
    const averageSale = totalSales / data.length;

    if (format === 'html') {
      report = `
        <html>
          <body>
            <h1>Sales Report</h1>
            <p>Total Sales: $${totalSales}</p>
            <p>Average Sale: $${averageSale.toFixed(2)}</p>
            <table>
              ${data.map(d => `<tr><td>${d.date}</td><td>$${d.amount}</td></tr>`).join('')}
            </table>
          </body>
        </html>
      `;
    } else if (format === 'pdf') {
      // PDF generation logic mixed in
      const pdf = new PDFDocument();
      pdf.text('Sales Report');
      pdf.text(`Total: $${totalSales}`);
      // ... more PDF logic
      report = pdf.output();
    } else if (format === 'csv') {
      report = 'Date,Amount\n';
      report += data.map(d => `${d.date},${d.amount}`).join('\n');
    } else if (format === 'excel') {
      // Excel generation logic
      // Adding new format requires modifying this class
    }

    return report;
  }
}

// Problems:
// 1. Adding JSON format requires modifying ReportGenerator
// 2. All format logic is tightly coupled
// 3. Testing is difficult
// 4. No way to extend without modification
```

## Good Example

```typescript
// Correct approach: Abstractions enable extension without modification

// Stable abstraction for report data
interface ReportData {
  readonly title: string;
  readonly generatedAt: Date;
  readonly summary: ReportSummary;
  readonly items: ReportItem[];
}

interface ReportSummary {
  readonly totalSales: number;
  readonly averageSale: number;
  readonly itemCount: number;
}

interface ReportItem {
  readonly date: string;
  readonly amount: number;
  readonly description?: string;
}

// Stable abstraction for formatters
interface ReportFormatter {
  readonly format: string;
  readonly mimeType: string;
  render(data: ReportData): string | Buffer | Promise<string | Buffer>;
}

// Stable abstraction for data sources
interface ReportDataSource<T> {
  fetch(criteria: ReportCriteria): Promise<T[]>;
  transform(rawData: T[]): ReportData;
}

// Report generator depends only on abstractions
class ReportGenerator {
  constructor(
    private formatters: Map<string, ReportFormatter> = new Map()
  ) {}

  registerFormatter(formatter: ReportFormatter): void {
    this.formatters.set(formatter.format, formatter);
  }

  async generate<T>(
    dataSource: ReportDataSource<T>,
    criteria: ReportCriteria,
    format: string
  ): Promise<GeneratedReport> {
    const formatter = this.formatters.get(format);
    if (!formatter) {
      throw new UnsupportedFormatError(format, this.getSupportedFormats());
    }

    const rawData = await dataSource.fetch(criteria);
    const reportData = dataSource.transform(rawData);
    const content = await Promise.resolve(formatter.render(reportData));

    return {
      content,
      mimeType: formatter.mimeType,
      format,
      generatedAt: new Date()
    };
  }

  getSupportedFormats(): string[] {
    return Array.from(this.formatters.keys());
  }
}

// Concrete formatters - each can be added without modifying ReportGenerator

class HtmlReportFormatter implements ReportFormatter {
  readonly format = 'html';
  readonly mimeType = 'text/html';

  render(data: ReportData): string {
    return `
      <!DOCTYPE html>
      <html>
        <head><title>${data.title}</title></head>
        <body>
          <h1>${data.title}</h1>
          <p>Generated: ${data.generatedAt.toISOString()}</p>
          <section class="summary">
            <p>Total Sales: $${data.summary.totalSales.toFixed(2)}</p>
            <p>Average Sale: $${data.summary.averageSale.toFixed(2)}</p>
          </section>
          <table>
            <thead><tr><th>Date</th><th>Amount</th></tr></thead>
            <tbody>
              ${data.items.map(item =>
                `<tr><td>${item.date}</td><td>$${item.amount}</td></tr>`
              ).join('')}
            </tbody>
          </table>
        </body>
      </html>
    `;
  }
}

class CsvReportFormatter implements ReportFormatter {
  readonly format = 'csv';
  readonly mimeType = 'text/csv';

  render(data: ReportData): string {
    const header = 'Date,Amount,Description';
    const rows = data.items.map(item =>
      `${item.date},${item.amount},"${item.description || ''}"`
    );
    return [header, ...rows].join('\n');
  }
}

class JsonReportFormatter implements ReportFormatter {
  readonly format = 'json';
  readonly mimeType = 'application/json';

  render(data: ReportData): string {
    return JSON.stringify(data, null, 2);
  }
}

// Adding Excel format - no modification to existing code
class ExcelReportFormatter implements ReportFormatter {
  readonly format = 'xlsx';
  readonly mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

  render(data: ReportData): Buffer {
    const workbook = new ExcelJS.Workbook();
    const sheet = workbook.addWorksheet(data.title);

    sheet.addRow(['Date', 'Amount', 'Description']);
    data.items.forEach(item => {
      sheet.addRow([item.date, item.amount, item.description || '']);
    });

    return workbook.xlsx.writeBuffer();
  }
}

// Concrete data source - can create new sources without modification
class SalesReportDataSource implements ReportDataSource<SalesData> {
  constructor(private salesRepository: SalesRepository) {}

  async fetch(criteria: ReportCriteria): Promise<SalesData[]> {
    return this.salesRepository.findByCriteria(criteria);
  }

  transform(rawData: SalesData[]): ReportData {
    const totalSales = rawData.reduce((sum, d) => sum + d.amount, 0);

    return {
      title: 'Sales Report',
      generatedAt: new Date(),
      summary: {
        totalSales,
        averageSale: rawData.length > 0 ? totalSales / rawData.length : 0,
        itemCount: rawData.length
      },
      items: rawData.map(d => ({
        date: d.date,
        amount: d.amount,
        description: d.productName
      }))
    };
  }
}

// Usage
const generator = new ReportGenerator();
generator.registerFormatter(new HtmlReportFormatter());
generator.registerFormatter(new CsvReportFormatter());
generator.registerFormatter(new JsonReportFormatter());
generator.registerFormatter(new ExcelReportFormatter());

const salesDataSource = new SalesReportDataSource(salesRepository);
const report = await generator.generate(salesDataSource, criteria, 'xlsx');
```

## Why

1. **Stable Core**: The `ReportGenerator` class and interfaces form a stable core that rarely changes.

2. **Independent Development**: Teams can develop new formatters or data sources independently.

3. **Composition Over Inheritance**: New functionality is added through composition (registering new implementations) rather than inheritance hierarchies.

4. **Testing**: Each component can be tested in isolation with mock implementations of interfaces.

5. **Flexibility**: The same generator works with any combination of data sources and formatters.

6. **Dependency Inversion**: High-level modules (ReportGenerator) don't depend on low-level modules (specific formatters), both depend on abstractions.

7. **Plugin System**: Natural foundation for a plugin architecture where third parties can add formatters.
