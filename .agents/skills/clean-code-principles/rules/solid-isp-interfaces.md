---
id: solid-isp-interfaces
title: SOLID - Interface Segregation (Small Interfaces)
category: solid-principles
priority: critical
tags: [SOLID, ISP, interface-segregation, cohesion]
related: [solid-isp-clients, solid-srp-class, core-separation-concerns]
---

# Interface Segregation Principle - Small Cohesive Interfaces

Interfaces should be small and cohesive, grouping only closely related methods. Split large interfaces into smaller, more focused ones.

## Bad Example

```typescript
// Anti-pattern: Large interface with unrelated methods grouped together

interface Document {
  // Content operations
  getContent(): string;
  setContent(content: string): void;
  appendContent(content: string): void;

  // Persistence operations
  save(): Promise<void>;
  load(id: string): Promise<void>;
  delete(): Promise<void>;

  // Export operations
  exportToPdf(): Promise<Buffer>;
  exportToWord(): Promise<Buffer>;
  exportToHtml(): Promise<string>;

  // Collaboration operations
  share(userId: string): Promise<void>;
  getCollaborators(): Promise<User[]>;
  addComment(comment: Comment): Promise<void>;
  getComments(): Promise<Comment[]>;

  // Version control
  createVersion(): Promise<Version>;
  getVersionHistory(): Promise<Version[]>;
  revertToVersion(versionId: string): Promise<void>;

  // Permissions
  setPermissions(permissions: Permissions): Promise<void>;
  getPermissions(): Promise<Permissions>;
  checkPermission(userId: string, action: string): Promise<boolean>;
}

// Simple note-taking app forced to implement everything
class SimpleNote implements Document {
  private content: string = '';

  getContent(): string { return this.content; }
  setContent(content: string): void { this.content = content; }
  appendContent(content: string): void { this.content += content; }

  // Forced to implement methods it doesn't need
  async save(): Promise<void> { throw new Error('Not supported'); }
  async load(id: string): Promise<void> { throw new Error('Not supported'); }
  async delete(): Promise<void> { throw new Error('Not supported'); }

  async exportToPdf(): Promise<Buffer> { throw new Error('Not supported'); }
  async exportToWord(): Promise<Buffer> { throw new Error('Not supported'); }
  async exportToHtml(): Promise<string> { throw new Error('Not supported'); }

  async share(userId: string): Promise<void> { throw new Error('Not supported'); }
  async getCollaborators(): Promise<User[]> { throw new Error('Not supported'); }
  async addComment(comment: Comment): Promise<void> { throw new Error('Not supported'); }
  async getComments(): Promise<Comment[]> { throw new Error('Not supported'); }

  async createVersion(): Promise<Version> { throw new Error('Not supported'); }
  async getVersionHistory(): Promise<Version[]> { throw new Error('Not supported'); }
  async revertToVersion(versionId: string): Promise<void> { throw new Error('Not supported'); }

  async setPermissions(permissions: Permissions): Promise<void> { throw new Error('Not supported'); }
  async getPermissions(): Promise<Permissions> { throw new Error('Not supported'); }
  async checkPermission(userId: string, action: string): Promise<boolean> { throw new Error('Not supported'); }
}
```

## Good Example

```typescript
// Correct approach: Small, cohesive interfaces

// Core content interface - the essence of a document
interface DocumentContent {
  getContent(): string;
  setContent(content: string): void;
}

// Extended content operations
interface EditableContent extends DocumentContent {
  appendContent(content: string): void;
  insertContent(position: number, content: string): void;
  deleteRange(start: number, end: number): void;
}

// Persistence operations
interface Persistable {
  save(): Promise<void>;
  load(id: string): Promise<void>;
}

interface Deletable {
  delete(): Promise<void>;
}

// Export capabilities
interface PdfExportable {
  exportToPdf(): Promise<Buffer>;
}

interface WordExportable {
  exportToWord(): Promise<Buffer>;
}

interface HtmlExportable {
  exportToHtml(): Promise<string>;
}

// Combine export interfaces when needed
interface FullyExportable extends PdfExportable, WordExportable, HtmlExportable {}

// Collaboration interfaces
interface Shareable {
  share(userId: string, permission: Permission): Promise<void>;
  unshare(userId: string): Promise<void>;
  getCollaborators(): Promise<Collaborator[]>;
}

interface Commentable {
  addComment(comment: Comment): Promise<void>;
  removeComment(commentId: string): Promise<void>;
  getComments(): Promise<Comment[]>;
}

// Version control
interface Versionable {
  createVersion(message?: string): Promise<Version>;
  getVersionHistory(): Promise<Version[]>;
  revertToVersion(versionId: string): Promise<void>;
}

// Permissions
interface PermissionControlled {
  setPermissions(permissions: Permissions): Promise<void>;
  getPermissions(): Promise<Permissions>;
  checkPermission(userId: string, action: string): Promise<boolean>;
}

// Simple note only implements what it needs
class SimpleNote implements DocumentContent {
  private content: string = '';

  getContent(): string {
    return this.content;
  }

  setContent(content: string): void {
    this.content = content;
  }
}

// Persistent note adds storage
class PersistentNote implements EditableContent, Persistable {
  private content: string = '';
  private id: string | null = null;

  constructor(private storage: StorageService) {}

  getContent(): string { return this.content; }
  setContent(content: string): void { this.content = content; }
  appendContent(content: string): void { this.content += content; }
  insertContent(position: number, content: string): void {
    this.content = this.content.slice(0, position) + content + this.content.slice(position);
  }
  deleteRange(start: number, end: number): void {
    this.content = this.content.slice(0, start) + this.content.slice(end);
  }

  async save(): Promise<void> {
    this.id = await this.storage.save(this.content);
  }

  async load(id: string): Promise<void> {
    this.content = await this.storage.load(id);
    this.id = id;
  }
}

// Full-featured collaborative document
class CollaborativeDocument implements
  EditableContent,
  Persistable,
  Deletable,
  FullyExportable,
  Shareable,
  Commentable,
  Versionable,
  PermissionControlled
{
  constructor(
    private storage: StorageService,
    private exportService: ExportService,
    private collaborationService: CollaborationService,
    private versionService: VersionService,
    private permissionService: PermissionService
  ) {}

  // Implement all methods with actual functionality
  // Each service handles its domain

  getContent(): string { /* ... */ }
  setContent(content: string): void { /* ... */ }
  appendContent(content: string): void { /* ... */ }
  insertContent(position: number, content: string): void { /* ... */ }
  deleteRange(start: number, end: number): void { /* ... */ }

  async save(): Promise<void> { /* delegate to storage */ }
  async load(id: string): Promise<void> { /* delegate to storage */ }
  async delete(): Promise<void> { /* delegate to storage */ }

  async exportToPdf(): Promise<Buffer> { return this.exportService.toPdf(this); }
  async exportToWord(): Promise<Buffer> { return this.exportService.toWord(this); }
  async exportToHtml(): Promise<string> { return this.exportService.toHtml(this); }

  async share(userId: string, permission: Permission): Promise<void> { /* ... */ }
  async unshare(userId: string): Promise<void> { /* ... */ }
  async getCollaborators(): Promise<Collaborator[]> { /* ... */ }

  async addComment(comment: Comment): Promise<void> { /* ... */ }
  async removeComment(commentId: string): Promise<void> { /* ... */ }
  async getComments(): Promise<Comment[]> { /* ... */ }

  async createVersion(message?: string): Promise<Version> { /* ... */ }
  async getVersionHistory(): Promise<Version[]> { /* ... */ }
  async revertToVersion(versionId: string): Promise<void> { /* ... */ }

  async setPermissions(permissions: Permissions): Promise<void> { /* ... */ }
  async getPermissions(): Promise<Permissions> { /* ... */ }
  async checkPermission(userId: string, action: string): Promise<boolean> { /* ... */ }
}

// Functions can accept only the interfaces they need
function renderDocument(doc: DocumentContent): void {
  console.log(doc.getContent());
}

async function exportToPdf(doc: PdfExportable): Promise<void> {
  const pdf = await doc.exportToPdf();
  // Send pdf...
}

function canUserEdit(doc: PermissionControlled, userId: string): Promise<boolean> {
  return doc.checkPermission(userId, 'edit');
}

// All document types work where their interfaces are expected
const simpleNote = new SimpleNote();
renderDocument(simpleNote); // Works!

const collabDoc = new CollaborativeDocument(/* ... */);
renderDocument(collabDoc); // Works!
await exportToPdf(collabDoc); // Works!
await canUserEdit(collabDoc, 'user123'); // Works!
```

## Why

1. **Cohesion**: Each interface groups related methods. `Commentable` is about comments, `Versionable` about versions.

2. **Flexibility**: Classes implement only the interfaces that match their capabilities.

3. **Composability**: Complex types are built by combining simple interfaces: `EditableContent & Persistable & Shareable`.

4. **No Empty Implementations**: No need for `throw new Error('Not supported')` - just don't implement the interface.

5. **Clear Capabilities**: Looking at a class's implemented interfaces tells you exactly what it can do.

6. **Easier Evolution**: Add new capabilities by creating new interfaces without modifying existing ones.

7. **Better Typing**: Functions declare exactly what they need. `exportToPdf(doc: PdfExportable)` is self-documenting.
