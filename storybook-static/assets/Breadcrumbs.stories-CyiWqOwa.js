import{j as e}from"./jsx-runtime-u17CrQMm.js";import{S as h}from"./index-yvwtsnL6.js";import{c as s}from"./utils-BQHNewu7.js";import{C as f}from"./chevron-right-Eh98bWBx.js";import{L as x}from"./index.esm-B_fzZxLI.js";import{r as B}from"./index-CAT4JDgN.js";import"./index-dmJXtalC.js";import"./createLucideIcon-Cq8_ABKM.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";function d({...r}){return e.jsx("nav",{"aria-label":"breadcrumb","data-slot":"breadcrumb",...r})}function l({className:r,...a}){return e.jsx("ol",{"data-slot":"breadcrumb-list",className:s("text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5",r),...a})}function m({className:r,...a}){return e.jsx("li",{"data-slot":"breadcrumb-item",className:s("inline-flex items-center gap-1.5",r),...a})}function p({asChild:r,className:a,...t}){const n=r?h:"a";return e.jsx(n,{"data-slot":"breadcrumb-link",className:s("hover:text-foreground transition-colors",a),...t})}function u({className:r,...a}){return e.jsx("span",{"data-slot":"breadcrumb-page",role:"link","aria-disabled":"true","aria-current":"page",className:s("text-foreground font-normal",r),...a})}function b({children:r,className:a,...t}){return e.jsx("li",{"data-slot":"breadcrumb-separator",role:"presentation","aria-hidden":"true",className:s("[&>svg]:size-3.5",a),...t,children:r??e.jsx(f,{})})}d.__docgenInfo={description:"",methods:[],displayName:"Breadcrumb"};l.__docgenInfo={description:"",methods:[],displayName:"BreadcrumbList"};m.__docgenInfo={description:"",methods:[],displayName:"BreadcrumbItem"};p.__docgenInfo={description:"",methods:[],displayName:"BreadcrumbLink",props:{asChild:{required:!1,tsType:{name:"boolean"},description:""}}};u.__docgenInfo={description:"",methods:[],displayName:"BreadcrumbPage"};b.__docgenInfo={description:"",methods:[],displayName:"BreadcrumbSeparator"};function g({breadcrumbs:r}){return e.jsx(e.Fragment,{children:r.length>0&&e.jsx(d,{children:e.jsx(l,{children:r.map((a,t)=>{const n=t===r.length-1;return e.jsxs(B.Fragment,{children:[e.jsx(m,{children:n?e.jsx(u,{children:a.title}):e.jsx(p,{asChild:!0,children:e.jsx(x,{href:a.href,children:a.title})})}),!n&&e.jsx(b,{})]},a.href)})})})})}g.__docgenInfo={description:"",methods:[],displayName:"Breadcrumbs",props:{breadcrumbs:{required:!0,tsType:{name:"Array",elements:[{name:"BreadcrumbItemType"}],raw:"BreadcrumbItemType[]"},description:""}}};const C={title:"Navigation/Breadcrumbs",component:g,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{breadcrumbs:{control:!1}}},o={args:{breadcrumbs:[{title:"Dashboard",href:"/"},{title:"Settings",href:"/settings"}]}},i={args:{breadcrumbs:[{title:"Dashboard",href:"/"},{title:"Organizations",href:"/organizations"},{title:"Acme Corp",href:"/organizations/1"}]}},c={args:{breadcrumbs:[{title:"Dashboard",href:"/"},{title:"Billing",href:"/billing"},{title:"Invoices",href:"/billing/invoices"},{title:"INV-2024-001",href:"/billing/invoices/1"}]}};o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  args: {
    breadcrumbs: [{
      title: 'Dashboard',
      href: '/'
    }, {
      title: 'Settings',
      href: '/settings'
    }]
  }
}`,...o.parameters?.docs?.source}}};i.parameters={...i.parameters,docs:{...i.parameters?.docs,source:{originalSource:`{
  args: {
    breadcrumbs: [{
      title: 'Dashboard',
      href: '/'
    }, {
      title: 'Organizations',
      href: '/organizations'
    }, {
      title: 'Acme Corp',
      href: '/organizations/1'
    }]
  }
}`,...i.parameters?.docs?.source}}};c.parameters={...c.parameters,docs:{...c.parameters?.docs,source:{originalSource:`{
  args: {
    breadcrumbs: [{
      title: 'Dashboard',
      href: '/'
    }, {
      title: 'Billing',
      href: '/billing'
    }, {
      title: 'Invoices',
      href: '/billing/invoices'
    }, {
      title: 'INV-2024-001',
      href: '/billing/invoices/1'
    }]
  }
}`,...c.parameters?.docs?.source}}};const k=["TwoLevels","ThreeLevels","Deep"];export{c as Deep,i as ThreeLevels,o as TwoLevels,k as __namedExportsOrder,C as default};
