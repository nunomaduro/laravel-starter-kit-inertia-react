import{j as a}from"./jsx-runtime-u17CrQMm.js";import{A as r,a as n,b as m}from"./avatar-hpw-nuF-.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-DegZLnM5.js";import"./index-BMy6K8Gt.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";import"./index-yvwtsnL6.js";import"./index-dmJXtalC.js";import"./index-BAEXrmqL.js";import"./utils-BQHNewu7.js";const j={title:"Data Display/Avatar",component:r,tags:["autodocs"],parameters:{layout:"centered"}},s={render:()=>a.jsxs(r,{children:[a.jsx(m,{src:"https://github.com/shadcn.png",alt:"shadcn"}),a.jsx(n,{children:"SC"})]})},t={render:()=>a.jsx(r,{children:a.jsx(n,{children:"JD"})})},i={render:()=>a.jsx("div",{className:"flex items-end gap-4",children:["size-6","size-8","size-10","size-12","size-16"].map((e,l)=>a.jsx(r,{className:e,children:a.jsx(n,{className:"text-[10px]",children:["XS","SM","MD","LG","XL"][l]})},e))})},c={render:()=>a.jsxs("div",{className:"flex -space-x-2",children:[["JD","AB","KR","ML"].map(e=>a.jsx(r,{className:"size-9 ring-2 ring-background",children:a.jsx(n,{className:"text-xs",children:e})},e)),a.jsx("div",{className:"flex size-9 items-center justify-center rounded-full bg-muted text-xs font-medium ring-2 ring-background",children:"+5"})]})};s.parameters={...s.parameters,docs:{...s.parameters?.docs,source:{originalSource:`{
  render: () => <Avatar>
            <AvatarImage src="https://github.com/shadcn.png" alt="shadcn" />
            <AvatarFallback>SC</AvatarFallback>
        </Avatar>
}`,...s.parameters?.docs?.source}}};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  render: () => <Avatar>
            <AvatarFallback>JD</AvatarFallback>
        </Avatar>
}`,...t.parameters?.docs?.source}}};i.parameters={...i.parameters,docs:{...i.parameters?.docs,source:{originalSource:`{
  render: () => <div className="flex items-end gap-4">
            {['size-6', 'size-8', 'size-10', 'size-12', 'size-16'].map((sz, i) => <Avatar key={sz} className={sz}>
                    <AvatarFallback className="text-[10px]">{['XS', 'SM', 'MD', 'LG', 'XL'][i]}</AvatarFallback>
                </Avatar>)}
        </div>
}`,...i.parameters?.docs?.source}}};c.parameters={...c.parameters,docs:{...c.parameters?.docs,source:{originalSource:`{
  render: () => <div className="flex -space-x-2">
            {['JD', 'AB', 'KR', 'ML'].map(initials => <Avatar key={initials} className="size-9 ring-2 ring-background">
                    <AvatarFallback className="text-xs">{initials}</AvatarFallback>
                </Avatar>)}
            <div className="flex size-9 items-center justify-center rounded-full bg-muted text-xs font-medium ring-2 ring-background">
                +5
            </div>
        </div>
}`,...c.parameters?.docs?.source}}};const f=["WithImage","WithFallback","Sizes","Group"];export{c as Group,i as Sizes,t as WithFallback,s as WithImage,f as __namedExportsOrder,j as default};
