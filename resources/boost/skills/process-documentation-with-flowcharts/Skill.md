---
name: process-documentation-with-flowcharts
description: Create and maintain process documentation with Mermaid flowcharts (TB) for each workflow. When changing application behaviour, always update the relevant process docs, flowcharts, and associated key files/tests lists.
---

# Process Documentation With Flowcharts

This codebase relies on **process docs + flowcharts** to understand and maintain complex backend workflows. When you make changes to application behaviour, you must also update the relevant documentation so it stays accurate.

## Non-negotiable rule

If you change any workflow behaviour (routing, events, jobs, observers, integrations, state transitions, edge-case handling), you must:
1. Update the relevant process documentation markdown file(s)
2. Update the Mermaid flowchart(s) inside those docs
3. Update the “Key files” and “Key tests” lists

Treat docs as part of the feature, not optional.

## Process doc structure

Each workflow has its own documentation file and title. Example workflows:

- `OrderProcess`
- `ReturnsProcess`
- `ShopOrderChangeRequestProcess`
- `TerminatingPurchaseOrderProcess`
- `ReceivingFulfillmentProcess`
- `CustomerReturnProcess`

### Required sections in every process doc

Each process doc must include:

1) **Title (exact workflow name)**
- Use `# OrderProcess` style titles.

2) **Purpose**
- What the process does and when it runs.

3) **Entry points**
- Webhooks, commands, scheduled jobs, UI actions, API endpoints, etc.

4) **Flowchart (required)**
- Always include a Mermaid diagram.
- Must be top-down and use `flowchart TB`.

**Swimlanes (required)**
- Must use swimlanes grouped by domain, specifically:
    - **Models** (e.g. `ShopOrder`, `FulfillmentOrder`, `PurchaseOrder`, `Fulfillment`, etc.)
    - **Integrations** (e.g. `Shopify Integration`, `Shipping Integration`, `SendCloud`, etc.)
    - **Key Job/Decision Process** (e.g. `Order Routing Job`, `Returns Routing Job`, etc.)
    - Other logical blocks where helpful (e.g. `Timeline`, `Auth/Validation`, `Notifications`, `Payments`)

**Entry points only rule (critical)**
- All flowchart *entries* must be **strict entry points** (things that enter the system), such as:
    - Webhooks received by your app
    - A user/admin action that triggers a job/command
    - A scheduled command (cron)
    - A message/queue event consumed by your app
- Do **not** draw “expected webhooks” as if they are caused by an earlier outgoing call in the same flowchart.
    - Example: If the app calls a Shipping provider API and later expects a webhook callback, the webhook is a **separate entry point**. It should be shown as its own entry point node (usually in an Integration lane), not directly connected as a deterministic next step from the outbound API call.
- If you need to show the relationship between outbound calls and later webhooks, do it in the **Detailed flow** text (and optionally via a labelled note/annotation), but keep the flowchart entry semantics correct.

5) **Detailed flow**
- A thorough step-by-step explanation matching the flowchart nodes.
- Include idempotency/guard rails: “already exists”, “missing IDs”, “ignore statuses”, etc.
- Call out transactional boundaries: “DB::transaction”, “saveQuietly”, “after commit”, etc.
- If an outbound integration call causes a later webhook, explain the linkage here.

6) **Key domain rules**
- The business rules and invariants that matter (routing decisions, cancellation semantics, return eligibility, etc.)

7) **Failure modes and retries**
- Common failure points and how they are handled (exceptions, retries/backoff, dead-letter patterns, logging).

8) **Key files**
- List the most important code files involved (jobs, actions, observers, models, controllers, integration clients).

9) **Key tests**
- List the most important test files that validate the workflow.
- Mention scenario builders / fakes where relevant.

## Flowchart rules (Mermaid)

### Mermaid format
- Always use Mermaid flowcharts, top-down, with `flowchart TB`.

### Swimlane rules
- Use `subgraph` lanes per domain and keep the grouping meaningful.
- Lanes must be grouped into the required categories:
    - Models lanes (one lane per major model domain)
    - Integrations lanes (one lane per integration boundary)
    - Key Job/Decision Process lanes (for orchestration jobs and routing/decision pipelines)
    - Other logical blocks as needed (timeline, auth, notifications, etc.)

Recommended lane naming patterns:
- Integrations:
    - `subgraph SHOPIFY["Shopify Integration lane"]`
    - `subgraph SHIPPING["Shipping Integration lane"]`
    - `subgraph SENDCLOUD["SendCloud lane"]`
- Models:
    - `subgraph SHOPORDER["ShopOrder lane"]`
    - `subgraph FO["FulfillmentOrder lane"]`
    - `subgraph PO["PurchaseOrder lane"]`
    - `subgraph FULFILLMENT["Fulfillment lane"]`
- Key jobs / decisions:
    - `subgraph ROUTING["Order Routing Job lane"]`
    - `subgraph RETURNS_ROUTING["Returns Routing Job lane"]`

### Node rules
- Each node should be short and verb-based:
    - `"Dispatch HandleNewReturnRequest"`
    - `"Authorize Shopify HMAC"`
    - `"Create ReverseFulfillmentOrders"`
- Use decision diamonds for branching:
    - `{Condition?}`
- Show idempotency and “stop” nodes explicitly:
    - `"idempotent stop"`
    - `"log and stop"`

### Required: use correct symbols (Mermaid v10+ shape syntax)

Use Mermaid’s shape syntax to convey meaning consistently:

- **Process (rect)**  
  `A@{ shape: rect, label: "This is a process" }`

- **Event (rounded)**  
  `A@{ shape: rounded, label: "This is an event" }`

- **Terminal point (stadium)**  
  `A@{ shape: stadium, label: "Terminal point" }`

- **Subprocess (subproc)**  
  `A@{ shape: subproc, label: "This is a subprocess" }`

- **Database (cyl)**  
  `A@{ shape: cyl, label: "Database" }`

- **Start (circle)**  
  `A@{ shape: circle, label: "Start" }`

- **Odd (odd)**  
  `A@{ shape: odd, label: "Odd shape" }`

- **Decision (diamond)**  
  `A@{ shape: diamond, label: "Decision" }`

- **Prepare conditional (hex)**  
  `A@{ shape: hex, label: "Prepare conditional" }`

- **Data input/output (lean-r)**  
  `A@{ shape: lean-r, label: "Input/Output" }`

- **Data input/output (lean-l)**  
  `A@{ shape: lean-l, label: "Output/Input" }`

- **Priority action (trap-b)**  
  `A@{ shape: trap-b, label: "Priority action" }`

- **Manual operation (trap-t)**  
  `A@{ shape: trap-t, label: "Manual operation" }`

Guidance on choosing symbols:
- Webhooks / inbound triggers → **Event (rounded)** as the entry point
- Dispatching a job → **Process (rect)** or **Subprocess (subproc)** (use subprocess for “job runs a multi-step pipeline”)
- Decision points → **Decision (diamond)**
- DB writes/reads that matter → **Database (cyl)** node (only when it clarifies the flow)
- End states (“ignore”, “idempotent stop”, “error stop”) → **Terminal (stadium)**
- “Prepare/resolve/configure” steps → **Prepare conditional (hex)**

### Required content
A workflow flowchart must include:
- Entry point(s) (as strict entry points)
- Key decisions/branches
- Job dispatches / observer triggers
- Integration calls (outbound) and separate webhook entry points (inbound)
- Terminal outcomes (success/ignore/stop/error)

## Documentation update checklist (must follow)

When code changes affect a workflow, update the doc by doing all of the below:

- [ ] Update flowchart nodes and edges to reflect the new behaviour
- [ ] Ensure entry nodes remain strict entry points (don’t chain “expected webhook” off an outbound call)
- [ ] Update the step-by-step narrative to match the flowchart
- [ ] Update “Key files” list if files changed / were added
- [ ] Update “Key tests” list if coverage changed / needs adding
- [ ] Add/adjust failure modes if new exceptions, retries, or idempotency rules were introduced
- [ ] Ensure naming matches the actual code (job names, event names, observer methods)

## Process doc template (copy/paste)

```markdown
    # OrderProcess
    
    ## Purpose
    Describe what this workflow does.
    
    ## Entry points
    - List the entry points (webhooks, actions, jobs, commands).
    
    ## Flowchart
    ```mermaid
    flowchart TB
      %% Lanes (group by domain)
      subgraph SHOPIFY["Shopify Integration lane"]
        direction TD
        A@{ shape: rounded, label: "Shopify orders/create webhook" }
      end
    
      subgraph ROUTING["Order Routing Job lane"]
        direction TD
        B@{ shape: rect, label: "Authorize webhook + dispatch ImportShopOrder" }
        C@{ shape: diamond, label: "Order exists locally?" }
        C1@{ shape: stadium, label: "idempotent stop" }
      end
    
      subgraph SHOPORDER["ShopOrder lane"]
        direction TD
        D@{ shape: cyl, label: "Upsert ShopOrder + ShopOrderLines" }
        E@{ shape: rect, label: "Fire domain event: accepted" }
      end
    
      subgraph FO["FulfillmentOrder lane"]
        direction TD
        F@{ shape: subproc, label: "Raise FulfillmentOrders" }
      end
    
      subgraph SHIPPING["Shipping Integration lane"]
        direction TD
        G@{ shape: rect, label: "Create shipment (outbound API call)" }
        %% IMPORTANT: inbound webhook is a separate entry point, not chained from G
        H@{ shape: rounded, label: "Shipping provider webhook: parcel_status_changed" }
      end
    
      %% Edges
      A --> B --> C
      C -- yes --> C1
      C -- no --> D --> E --> F --> G
      H --> F
    ```

    ## Detailed flow
	1.	Step-by-step narrative, matching flowchart nodes.
	2.	Explain idempotency, transactional boundaries, and after-commit observers.
	3.	If outbound integration calls lead to later webhooks, describe that linkage here.

    ## Key domain rules
    - Business rules and invariants.

    ## Failure modes and retries
    - Common failure points and how they are handled.

    ## Key files
    - app/Modules/...
    - app/Observers/...
    - app/Integrations/...
    - app/Models/...

    ## Key tests
    - tests/Feature/...
    - tests/Unit/...

```
